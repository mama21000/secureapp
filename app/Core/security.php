<?php
declare(strict_types=1);

namespace App\Core;

final class Security {
  public static function init(): void {
    // Harden PHP session behavior
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    $secure = Config::env('COOKIE_SECURE', '0') === '1';
    ini_set('session.cookie_secure', $secure ? '1' : '0');
    // Lax blocks most CSRF while keeping login flows OK; you still implement CSRF token for POST.
    ini_set('session.cookie_samesite', 'Lax');

    // Reduce fingerprinting
    ini_set('expose_php', '0');

    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_name('SECSESSID');
      session_start();
    }

    // Basic anti-fixation: regenerate occasionally
    if (!isset($_SESSION['_regen'])) {
      $_SESSION['_regen'] = time();
    } elseif (time() - (int)$_SESSION['_regen'] > 300) {
      session_regenerate_id(true);
      $_SESSION['_regen'] = time();
    }
  }

  public static function sendHeaders(): void {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    header('Permissions-Policy: geolocation=(), camera=(), microphone=()');

    // Conservative CSP (adjust if you add external CDNs)
    header("Content-Security-Policy: 
  default-src 'self';
  img-src 'self' data: blob:;
  base-uri 'self';
  frame-ancestors 'none';
  form-action 'self';
  object-src 'none';
  style-src 'self' 'unsafe-inline';
  script-src 'self';
");
  

    // Prevent caching of sensitive pages
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
  }

  public static function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }

  /**
     * Validate password against strong password policy.
     *
     * Policy:
     * - Minimum 10 characters
     * - Maximum 64 characters
     * - At least one uppercase letter
     * - At least one digit
     * - At least one special character
     *
     * @param string $password Raw password input
     * @return array{valid: bool, message: string}
     */
    public static function validatePassword(string $password): array
    {
        $policyMessage = "Password must be between 10 and 64 characters long and include at least one uppercase letter, one number, and one special character.";

        if (
            strlen($password) < 10 ||
            strlen($password) > 64 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[0-9]/', $password) ||
            !preg_match('/[\W_]/', $password)
        ) {
            return ['valid' => false, 'message' => $policyMessage];
        }

        return ['valid' => true, 'message' => 'OK'];
    }
}

