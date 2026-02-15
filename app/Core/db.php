<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class DB {
  private static ?PDO $pdo = null;

  public static function pdo(): PDO {
    if (self::$pdo) return self::$pdo;

    $host = Config::env('DB_HOST', 'db');
    $db   = Config::env('DB_NAME', 'secureapp');
    $user = Config::env('DB_USER', 'secureuser');
    $pass = Config::env('DB_PASS', 'securepass');

    $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";

    try {
      self::$pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
      ]);
      return self::$pdo;
    } catch (PDOException $e) {
      http_response_code(500);
      echo "Database connection failed.";
      exit;
    }
  }
}

