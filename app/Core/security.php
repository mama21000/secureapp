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
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'; object-src 'none'");

    // Prevent caching of sensitive pages
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
  }

  public static function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}

