<?php
declare(strict_types=1);

namespace App\Core;

final class Security
{
    private const SESSION_IDLE_TIMEOUT = 10;     // 15 minutes
    private const SESSION_ABSOLUTE_TIMEOUT = 20; // 8 hours

    public static function init(): void
    {
        // -------------------------------------------------
        // 1. Enforce HTTPS (Fail Fast)
        // -------------------------------------------------
        // (Uncomment for Production/Docker)
        /*
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
            http_response_code(400);
            exit('HTTPS Required');
        }
        */

        // -------------------------------------------------
        // 2. Secure Session Configuration
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
        // 3. Session Timeout and Rotation
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
        // 1. Clear session array
        $_SESSION = [];

        // 2. Delete the cookie
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

        // 3. Destroy session
        session_destroy();

        // 4. Start a NEW session just to store the flash message
        // (We must restart because we just destroyed the old one)
        session_start();
        session_regenerate_id(true);
        $_SESSION['flash_info'] = 'Your session has expired. Please login again.';

        // 5. Redirect to Login
        header("Location: /login");
        exit;
    }

    public static function sendHeaders(): void
    {
        // -------------------------------------------------
        // 4. HSTS
        // -------------------------------------------------
        // header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        if ($isHttps) {
            header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
        }

        // -------------------------------------------------
        // 5. Clickjacking Protection
        // -------------------------------------------------
        header('X-Frame-Options: DENY');
        // header('Content-Security-Policy: frame-ancestors \'none\';');

        // -------------------------------------------------
        // 6. MIME Sniffing Protection
        // -------------------------------------------------
        header('X-Content-Type-Options: nosniff');

        // -------------------------------------------------
        // 7. Privacy Controls
        // -------------------------------------------------
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // -------------------------------------------------
        // 8. Browser Isolation Headers (Modern)
        // -------------------------------------------------
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Cross-Origin-Resource-Policy: same-origin');
        header('Cross-Origin-Embedder-Policy: require-corp');

        // -------------------------------------------------
        // 9. Permissions Policy
        // -------------------------------------------------
        header(
            'Permissions-Policy: geolocation=(), camera=(), microphone=(), payment=(), usb=(), gyroscope=()'
        );

        // -------------------------------------------------
        // 10. Strong CSP with Nonce
        // -------------------------------------------------
        // $nonce = base64_encode(random_bytes(16));

        // $_SESSION['csp_nonce'] = $nonce;
        if (!isset($_SESSION['csp_nonce'])) {
            $_SESSION['csp_nonce'] = base64_encode(random_bytes(16));
        }
        $nonce = $_SESSION['csp_nonce'];

        $csp =
            "default-src 'self'; " .

            // 1. SCRIPTS: STRICT (Nonce only)
            // Blocks all XSS (<script>...</script>).
            "script-src 'self' 'nonce-{$nonce}'; " .
            
            // 2. STYLE BLOCKS: STRICT (Nonce only)
            // Blocks injected <style>...</style> tags. 
            // YOUR REFACTORING WORK PROTECTS THIS.
            "style-src 'self' 'nonce-{$nonce}'; " .
            
            // 3. STYLE ATTRIBUTES: RELAXED
            // Allows style="..." for Bootstrap JS positioning.
            // This is a "Defense in Depth" compromise.
            "style-src-attr 'self' 'unsafe-inline'; " .

            // "script-src 'self' 'nonce-{$nonce}'; " .	// STRICT: Only allows scripts with nonce
            // "style-src 'self' 'nonce-{$nonce}'; " .		// STRICT: Only allows styles with nonce
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
        // 11. Anti-Caching for Sensitive Pages
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
