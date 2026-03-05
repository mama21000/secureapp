<?php
declare(strict_types=1);

namespace App\Core;

final class Security
{
    private const SESSION_IDLE_TIMEOUT = 900;     // 15 minutes
    private const SESSION_ABSOLUTE_TIMEOUT = 28800; // 8 hours

    public static function init(): void
    {
        // -------------------------------------------------
        // Enforce HTTPS (Fail Fast)
        // -------------------------------------------------
        // (Uncomment for Production/Docker)
        /*
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
            http_response_code(400);
            exit('HTTPS Required');
        }
        */

        // -------------------------------------------------
        // Secure Session Configuration
        // -------------------------------------------------
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        
        // ini_set('session.cookie_secure', '1');
        // Conditional Secure Flag (Auto-detects HTTPS)
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        ini_set('session.cookie_secure', $isHttps ? '1' : '0');
        
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('expose_php', '0');

        session_name('__Secure-ID'); // Secure prefix requires Secure flag
        session_start();

        // -------------------------------------------------
        // Session Timeout and Rotation
        // -------------------------------------------------
        $now = time();

        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = $now;
        }

        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = $now;
        }

        // Idle timeout
        if ($now - $_SESSION['last_activity'] > self::SESSION_IDLE_TIMEOUT) {
            self::destroySession();
        }

        // Absolute timeout
        if ($now - $_SESSION['created'] > self::SESSION_ABSOLUTE_TIMEOUT) {
            self::destroySession();
        }

        $_SESSION['last_activity'] = $now;

        // Rotate ID every 5 minutes
        if (!isset($_SESSION['_regen'])) {
            $_SESSION['_regen'] = $now;
        } elseif ($now - $_SESSION['_regen'] > 300) {
            session_regenerate_id(true);
            $_SESSION['_regen'] = $now;
        }
    }

    private static function destroySession(): void
    {
        // Clear session array
        $_SESSION = [];

        // Delete the cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                true,
                true
            );
        }

        // Destroy session
        session_destroy();

        // Start a NEW session just to store the flash message
        session_start();
        session_regenerate_id(true);
        $_SESSION['flash_info'] = 'Your session has expired. Please login again.';

        // Redirect to Login
        header("Location: /login");
        exit;
    }

    public static function sendHeaders(): void
    {
        // -------------------------------------------------
        // HSTS
        // -------------------------------------------------
        // header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        if ($isHttps) {
            header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
        }

        // -------------------------------------------------
        // Clickjacking Protection
        // -------------------------------------------------
        header('X-Frame-Options: DENY');

        // -------------------------------------------------
        // MIME Sniffing Protection
        // -------------------------------------------------
        header('X-Content-Type-Options: nosniff');

        // -------------------------------------------------
        // Privacy Controls
        // -------------------------------------------------
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // -------------------------------------------------
        // Browser Isolation Headers (Modern)
        // -------------------------------------------------
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Cross-Origin-Resource-Policy: same-origin');
        header('Cross-Origin-Embedder-Policy: require-corp');

        // -------------------------------------------------
        // Permissions Policy
        // -------------------------------------------------
        header(
            'Permissions-Policy: geolocation=(), camera=(), microphone=(), payment=(), usb=(), gyroscope=()'
        );

        // -------------------------------------------------
        // Strong CSP with Nonce
        // -------------------------------------------------
        if (!isset($_SESSION['csp_nonce'])) {
            $_SESSION['csp_nonce'] = base64_encode(random_bytes(16));
        }
        $nonce = $_SESSION['csp_nonce'];

        $csp =
            "default-src 'self'; " .

            // Blocks all XSS (<script>...</script>).
            "script-src 'self' 'nonce-{$nonce}'; " .    // STRICT: Only allows scripts with nonce
        
            // Blocks injected <style>...</style> tags.
            "style-src 'self' 'nonce-{$nonce}'; " .     // STRICT: Only allows styles with nonce
            
            // Allows style="..." for Bootstrap JS positioning.
            "style-src-attr 'self' 'unsafe-inline'; " .

            "img-src 'self' data:; " .
            "font-src 'self'; " .
            "connect-src 'self'; " .
            "object-src 'none'; " .
            "base-uri 'self'; " .
            "form-action 'self'; " .
            "frame-ancestors 'none'; " .
            "upgrade-insecure-requests;";

        header("Content-Security-Policy: $csp");

        // -------------------------------------------------
        // Anti-Caching for Sensitive Pages
        // -------------------------------------------------
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    public static function e(string $s): string
    {
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
