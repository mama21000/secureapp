<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\DB;
use App\Core\CSRF;
use App\Core\Auth;
use App\Core\Security;
use App\Models\UserModel;
use App\Core\FileLogger;

final class AuthController {

  public function showRegister(): void {
    Response::view('auth/register');
  }

  public function register(): void {
    CSRF::verify();

    $username = trim((string)($_POST['username'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm_password = (string)($_POST['confirm_password'] ?? '');


    // Validation
    if (!preg_match('/^[a-zA-Z0-9_]{3,40}$/', $username)) {
      FileLogger::warning("Registration failed: Invalid username '{$username}'");
      Response::view('auth/register', ['error' => 'Invalid username format.']);
      return;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
      FileLogger::warning("Registration failed: Invalid email '{$email}'");
      Response::view('auth/register', ['error' => 'Invalid email address.']);
      return;
    }
    
    // validate password according to password policy.
    $password_validation_result = Security::validatePassword($password);
    if (!$password_validation_result['valid']) {
        $error = $password_validation_result['message'];
        FileLogger::warning("Registration failed: {$error}");
        Response::view('auth/register', ['error' => $error]);
        return;
    }

    //  Confirm password check
    if ($password !== $confirm_password) {
      FileLogger::warning("Registration failed: Passwords do not match for '{$username}'");
      Response::view('auth/register', ['error' => 'Passwords do not match.']);
      return;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $pdo = DB::pdo();

    // Enforce uniqueness safely. Database Insert (Atomic)
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, balance) VALUES (?, ?, ?, 100)");
    try {
      $stmt->execute([$username, $email, $hash]);
    } catch (\PDOException $e) {
      // Don't reveal which one (username or email) failed for privacy (User Enumeration prevention)
      FileLogger::warning("Registration failed: Duplicate entry for '{$username}' or '{$email}'");
      Response::view('auth/register', ['error' => 'Username or email already exists.']);
      return;
    }

    FileLogger::info("User registered successfully: {$username}");
    
    // Redirect to login with a success flash message (requires Session to be active)
    $_SESSION['flash_success'] = 'Registration successful! Please login.';
    Response::redirect('/login');
  }

  public function showLogin(): void {
    Response::view('auth/login');
  }

  public function login(): void {
      CSRF::verify();

      $USER_LIMIT = 5;      // Lock specific username string after 5 tries
      $IP_LIMIT   = 15;     // Lock IP after 15 total failures
      $LOCKOUT_MINUTES = 10;

      $username = trim((string)($_POST['username'] ?? ''));
      $password = (string)($_POST['password'] ?? '');
      $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
      
      $pdo = DB::pdo();

      // ---------------------------------------------------
      // BRUTE FORCE PROTECTION (IP Level)
      // ---------------------------------------------------
      $stmtIP = $pdo->prepare("
          SELECT COUNT(*) as failures, MIN(attempted_at) as first_fail 
          FROM login_attempts 
          WHERE success = 0 AND ip = ? 
          AND attempted_at > (NOW() - INTERVAL $LOCKOUT_MINUTES MINUTE)
      ");
      $stmtIP->execute([$ip]);
      $ipStats = $stmtIP->fetch();

      if (($ipStats['failures'] ?? 0) >= $IP_LIMIT) {
          FileLogger::warning("Login blocked: Too many failed attempts from IP {$ip}");
          $this->enforceLockout($pdo, $ip, $username, $ipStats['first_fail'], $LOCKOUT_MINUTES, 'IP');
          return;
      }

      // ---------------------------------------------------
      // BRUTE FORCE PROTECTION (User Level, even if 'ghost_user' doesn't exist)
      // ---------------------------------------------------
      $stmtUser = $pdo->prepare("
          SELECT COUNT(*) as failures, MIN(attempted_at) as first_fail 
          FROM login_attempts 
          WHERE success = 0 AND username = ? 
          AND attempted_at > (NOW() - INTERVAL $LOCKOUT_MINUTES MINUTE)
      ");
      $stmtUser->execute([$username]);
      $userStats = $stmtUser->fetch();

      if (($userStats['failures'] ?? 0) >= $USER_LIMIT) {
          FileLogger::warning("Login blocked: Too many failed attempts for username '{$username}'");
          $this->enforceLockout($pdo, $ip, $username, $userStats['first_fail'], $LOCKOUT_MINUTES, 'USER');
          return;
      }

      // ---------------------------------------------------
      // AUTHENTICATION
      // ---------------------------------------------------
      $user = UserModel::findForAuth($username);
      
      // Verify hash
      $ok = $user && password_verify($password, $user['password_hash']);

      // Log the attempt immediately
      $ins = $pdo->prepare("INSERT INTO login_attempts (ip, username, success) VALUES (?, ?, ?)");
      $ins->execute([$ip, $username ?: null, $ok ? 1 : 0]);

      if (!$ok) {
          FileLogger::warning("Login failed for username '{$username}' from IP {$ip}");
          
          // Delay (prevents timing attacks)
          usleep(random_int(300000, 500000)); // 300ms - 500ms
          
          Response::view('auth/login', ['error' => 'Invalid credentials.']);
          return;
      }

      // ---------------------------------------------------
      // SUCCESS: BIND SESSION
      // ---------------------------------------------------
      Auth::login((int)$user['id'], (string)$user['username']);
      
      Response::redirect('/');
  }

  // To handle the 'Tar Pit' delay and Messaging
  private function enforceLockout($pdo, $ip, $username, $first_fail_time, $minutes, $type): void {
      // Log the blocked attempt (Extends the ban window)
      $ins = $pdo->prepare("INSERT INTO login_attempts (ip, username, success) VALUES (?, ?, 0)");
      $ins->execute([$ip, $username]);

      // Calculate Time Left
      $expiry = strtotime($first_fail_time) + ($minutes * 60);
      $left = ceil(($expiry - time()) / 60);
      if ($left < 1) $left = 1;

      // Tar Pit Delay (Wastes attacker's time)
      sleep(1); 

      // Distinct Messages
      $msg = ($type === 'IP') 
          ? "Too many attempts from this IP address. Please wait $left minute(s) before trying again."
          : "Too many attempts for this User. Please wait $left minute(s) before trying again.";
      $entity = ($type === 'IP') ? $ip : $username;
      FileLogger::warning("Lockout enforced for {$type} '{$entity}' due to multiple failed login attempts.");
      Response::view('auth/login', ['error' => $msg]);
  }

  public function logout(): void {
    // CSRF verification on logout is done if forms send _csrf.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        CSRF::verify();
    }
    
    Auth::logout();
    Response::redirect('/login');
  }
}