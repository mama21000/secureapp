<?php
declare(strict_types=1);

namespace App\Core;

final class Logger
{
  /**
   * Middleware: logs the current request.
   * Never throws errors to users (attackers shouldn’t learn anything from logger failures).
   */
  public static function requestLog(): void
  {
    // Only log "real" endpoints; skip assets to reduce noise
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    if (str_starts_with($path, '/assets/')) return;

    $ip = self::clientIp();
    $username = Auth::username(); // null if guest

    // Keep it bounded to match DB column
    if (mb_strlen($path) > 255) $path = mb_substr($path, 0, 255);

    try {
      $pdo = DB::pdo();
      $stmt = $pdo->prepare("
        INSERT INTO activity_logs (webpage, username, ip)
        VALUES (?, ?, ?)
      ");
      $stmt->execute([$path, $username, $ip]);
    } catch (\Throwable $e) {
      // Fail closed (do nothing). Don’t leak and don’t break app.
    }
  }

  /**
   * Use REMOTE_ADDR by default (most correct for a student VM).
   * If later you sit behind a trusted reverse proxy, you can extend this carefully.
   */
  private static function clientIp(): string
  {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // Normalize and validate
    if (!is_string($ip) || $ip === '') return 'unknown';
    if (filter_var($ip, FILTER_VALIDATE_IP) === false) return 'unknown';

    // Ensure it fits VARCHAR(45)
    if (strlen($ip) > 45) $ip = substr($ip, 0, 45);
    return $ip;
  }
}

