<?php
declare(strict_types=1);

namespace App\Core;
use App\Core\FileLogger;

final class Auth {
  
  
  // -------------------------------------------------------------------
  //  SESSION BINDING
  // ------------------------------------------------------------------- 
  /**
   * Generates a hash of the user's IP and User Agent. This locks the session to a specific device and network.
   */
  private static function generateFingerprint(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    // We hash it so we don't store PII directly in session if not needed
    return hash('sha256', $ip . '|' . $agent);
  }

  // -------------------------------------------------------------------
  //  Core Auth Methods
  // -------------------------------------------------------------------

  public static function userId(): ?int {
    // Before returning ID, verify if the fingerprint matches!
    if (!self::verifyFingerprint()) {
        return null;
    }
    return isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
  }

  public static function username(): ?string {
    if (!self::verifyFingerprint()) {
        return null;
    }
    return isset($_SESSION['uname']) ? (string)$_SESSION['uname'] : null;
  }

  public static function check(): bool {
    return self::userId() !== null;
  }

  /**
   * Enforces login. If session is missing OR fingerprint doesn't match,
   * it redirects to login.
   */
  public static function requireLogin(): void {
    if (!self::check()) {
      // If check() failed, it might be a hijacking attempt. Force logout to clean up.
      self::logout(); 
      header('Location: /login');
      exit;
    }
  }

  /**
   * Logs the user in and applies security bindings.
   */
  public static function login(int $uid, string $uname): void {
    // Regenerate ID - Prevents Session Fixation
    session_regenerate_id(true);

    $_SESSION['uid'] = $uid;
    $_SESSION['uname'] = $uname;
    
    // Store Timestamp
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();

    // Store Fingerprint
    $_SESSION['fingerprint'] = self::generateFingerprint();

    FileLogger::info("User logged in: {$uname} (ID: {$uid})");
  }

  public static function logout(): void {
    // Clear array
    $_SESSION = [];

    // Delete the cookie from the browser
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(
          session_name(), 
          '', 
          time() - 42000,
          $params["path"], 
          $params["domain"],
          (bool)$params["secure"],
          (bool)$params["httponly"]
      );
    }

    // Kill file on server
    session_destroy();
    
    // Start a fresh session for flash messages on login page
    session_start();
    session_regenerate_id(true);

    FileLogger::info("User logged out");
  }

  /**
   * Internal check to ensure session hasn't been hijacked.
   */
  private static function verifyFingerprint(): bool {
    // If user isn't logged in, no fingerprint to check
    if (!isset($_SESSION['uid'])) {
        return false;
    }

    // If session has no fingerprint (old session?), fail safely
    if (!isset($_SESSION['fingerprint'])) {
        return false;
    }

    $current = self::generateFingerprint();
    
    // CHECK: Does the current requester match the person who logged in?
    if (!hash_equals($_SESSION['fingerprint'], $current)) {
        FileLogger::warning("Session Hijacking Detected! IP/Agent mismatch for User ID {$_SESSION['uid']}");
        return false;
    }

    return true;
  }
}