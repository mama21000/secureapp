<?php

namespace App\Core;

/**
 * FileLogger provides structured JSON logging for the application.
 *
 * Each log entry contains contextual metadata such as:
 * - timestamp
 * - log level
 * - correlation ID
 * - client IP
 * - user ID
 * - HTTP method
 * - request path
 * - message
 *
 * Logs are written using PHP's error_log(), which is configured
 * to write into the application's log file.
 */
class FileLogger
{
    /**
     * Writes a structured JSON log entry.
     *
     * @param string $level   Log severity level (INFO, WARNING, ERROR)
     * @param string $message Human-readable log message
     */
    private static function write(string $level, string $message): void
    {
        $log = [
                'timestamp' => date('c'),
                'level' => $level,
                'cid' => RequestContext::cid(),
                'ip' => RequestContext::ip(),
                'uid' => RequestContext::userId(),
                'uname' => RequestContext::username(),
                'method' => RequestContext::method(),
                'path' => RequestContext::path(),
                'message' => $message
            ];

        error_log(json_encode($log));
    }

    /**
     * Logs an informational message.
     */
    public static function info(string $message): void
    {
        self::write('INFO', $message);
    }

    /**
     * Logs a warning message.
     */
    public static function warning(string $message): void
    {
        self::write('WARNING', $message);
    }

    /**
     * Logs an error message.
     */
    public static function error(string $message): void
    {
        self::write('ERROR', $message);
    }
}