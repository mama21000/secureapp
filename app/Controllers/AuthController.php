<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\DB;
use App\Core\CSRF;
use App\Core\Auth;

final class AuthController {

  public function showRegister(): void {
    Response::view('auth/register');
  }

  public function register(): void {
    CSRF::verify();

    $username = trim((string)($_POST['username'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (!preg_match('/^[a-zA-Z0-9_]{3,40}$/', $username)) {
      Response::view('auth/register', ['error' => 'Invalid username.']);
      return;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
      Response::view('auth/register', ['error' => 'Invalid email.']);
      return;
    }
    if (strlen($password) < 10 || strlen($password) > 200) {
      Response::view('auth/register', ['error' => 'Password must be at least 10 characters.']);
      return;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $pdo = DB::pdo();
    // Enforce uniqueness safely
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, balance) VALUES (?, ?, ?, 100)");
    try {
      $stmt->execute([$username, $email, $hash]);
    } catch (\PDOException $e) {
      Response::view('auth/register', ['error' => 'Username/email already exists.']);
      return;
    }

    Response::redirect('/login');
  }

  public function showLogin(): void {
    Response::view('auth/login');
  }

  public function login(): void {
    CSRF::verify();

    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    // Basic rate-limit (simple but effective): block if too many attempts from IP recently
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $pdo = DB::pdo();
    $check = $pdo->prepare("SELECT COUNT(*) AS c FROM login_attempts WHERE ip=? AND attempted_at > (NOW() - INTERVAL 10 MINUTE)");
    $check->execute([$ip]);
    $count = (int)($check->fetch()['c'] ?? 0);
    if ($count >= 10) {
      http_response_code(429);
      echo "Too many attempts. Try later.";
      return;
    }

    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username=? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    $ok = $user && password_verify($password, $user['password_hash']);

    // Record attempt (do not reveal whether username exists)
    $ins = $pdo->prepare("INSERT INTO login_attempts (ip, username) VALUES (?, ?)");
    $ins->execute([$ip, $username ?: null]);

    if (!$ok) {
      Response::view('auth/login', ['error' => 'Invalid credentials.']);
      return;
    }

    Auth::login((int)$user['id'], (string)$user['username']);
    Response::redirect('/');
  }

  public function logout(): void {
    CSRF::verify();
    Auth::logout();
    Response::redirect('/');
  }
}

