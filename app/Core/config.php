<?php
declare(strict_types=1);

namespace App\Core;

final class Config {
  public static function env(string $key, ?string $default=null): string {
    $v = getenv($key);
    return ($v === false || $v === '') ? ($default ?? '') : $v;
  }

  public static function isProd(): bool {
    return self::env('APP_ENV', 'prod') === 'prod';
  }
}

