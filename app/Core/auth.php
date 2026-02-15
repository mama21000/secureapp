<?php
declare(strict_types=1);

namespace App\Core;

final class Auth {
  public static function userId(): ?int {
    return isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
  }

  public static function username(): ?string {
    return isset($_SESSION['uname']) ? (string)$_SESSION['uname'] : null;
  }

  public static function check(): bool {
    return self::userId() !== null;
  }

  public static function requireLogin(): void {
    if (!self::check()) {
      header('Location: /login');
      exit;
    }
  }

  public static function login(int $uid, string $uname): void {
    session_regenerate_id(true);
    $_SESSION['uid'] = $uid;
    $_SESSION['uname'] = $uname;
    $_SESSION['_regen'] = time();
  }

  public static function logout(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        (bool)$params["secure"], (bool)$params["httponly"]
      );
    }
    session_destroy();
  }
}

