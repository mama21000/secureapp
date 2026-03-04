<?php
declare(strict_types=1);

namespace App\Core;
use App\Core\FileLogger;

final class CSRF {
  public static function token(): string {
    if (empty($_SESSION['_csrf'])) {
      $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
  }

  public static function verify(): void {
    $t = $_POST['_csrf'] ?? '';
    if (!is_string($t) || !hash_equals((string)($_SESSION['_csrf'] ?? ''), $t)) {
      http_response_code(403);
      echo "Invalid CSRF token.";
      FileLogger::warning("CSRF token verification failed");
      exit;
    }
  }
}

