<?php

namespace App\Core;

/**
 * RequestContext stores metadata related to the current HTTP request.
 *
 * It provides contextual information that can be attached to every log entry,
 * allowing easier debugging, security analysis, and tracing of requests.
 *
 * The context includes:
 * - correlation ID (unique identifier for each request)
 * - client IP address
 * - authenticated user ID
 * - HTTP request method
 * - requested path
 */
class RequestContext
{
    /** @var string Unique correlation ID for the request */
    private static string $cid;

    /**
     * Initializes the request context.
     *
     * This method should be called once at the start of every request
     * (usually inside bootstrap.php).
     *
     * It generates a cryptographically secure correlation ID that
     * allows tracing all logs belonging to a single request.
     */
    public static function init(): void
    {
        self::$cid = bin2hex(random_bytes(16));
    }

    /**
     * Returns the correlation ID for the current request.
     */
    public static function cid(): string
    {
        return self::$cid ?? 'unknown';
    }

    /**
     * Returns the client IP address.
     */
    public static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Returns the authenticated user ID.
     * If the user is not logged in, "guest" is returned.
     */
    public static function userId(): string
    {
        return $_SESSION['uid'] ?? 'guest';
    }

    /**
    * Returns the logged-in username.
    * Returns 'guest' if no authenticated user exists.
    */
    public static function username(): string
    {
        return $_SESSION['uname'] ?? 'guest';
    }

    /**
     * Returns the HTTP method used for the request.
     */
    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'unknown';
    }

    /**
     * Returns the requested URI path.
     */
    public static function path(): string
    {
        return $_SERVER['REQUEST_URI'] ?? 'unknown';
    }
}