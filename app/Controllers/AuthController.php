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

  //  Confirm password check
  if ($password !== $confirm_password) {
    FileLogger::warning("Registration failed: Passwords do not match for '{$username}'");
    Response::view('auth/register', ['error' => 'Passwords do not match.']);
    return;
  }

    if (!preg_match('/^[a-zA-Z0-9_]{3,40}$/', $username)) {
      FileLogger::warning("Registration failed: Invalid username '{$username}'");
      Response::view('auth/register', ['error' => 'Invalid username.']);
      return;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
      FileLogger::warning("Registration failed: Invalid email '{$email}'");
      Response::view('auth/register', ['error' => 'Invalid email.']);
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

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $pdo = DB::pdo();
    // Enforce uniqueness safely
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, balance) VALUES (?, ?, ?, 100)");
    try {
      $stmt->execute([$username, $email, $hash]);
    } catch (\PDOException $e) {
      FileLogger::warning("Registration failed: Username/email already exists.");
      Response::view('auth/register', ['error' => 'Username/email already exists.']);
      return;
    }

    FileLogger::info("User registered successfully: {$username}");
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

      // CHECK IP LOCKOUT
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

      // CHECK USERNAME LOCKOUT (Even if 'ghost_user' doesn't exist)
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

      // AUTHENTICATE
      $user = UserModel::findForAuth($username);
      $ok = $user && password_verify($password, $user['password_hash']);

      // LOG RESULT
      $ins = $pdo->prepare("INSERT INTO login_attempts (ip, username, success) VALUES (?, ?, ?)");
      $ins->execute([$ip, $username ?: null, $ok ? 1 : 0]);

      if (!$ok) {
          FileLogger::warning("Login failed for username '{$username}' from IP {$ip}");
          usleep(500000); // 0.5s delay
          Response::view('auth/login', ['error' => 'Invalid credentials.']);
          return;
      }

      // SUCCESS
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
    CSRF::verify();
    Auth::logout();
    Response::redirect('/');
  }
}