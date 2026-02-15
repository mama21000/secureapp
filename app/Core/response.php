<?php
declare(strict_types=1);

namespace App\Core;

final class Response {
  public static function view(string $view, array $data = []): void {
    extract($data, EXTR_SKIP);
    require __DIR__ . '/../Views/layout/header.php';
	require __DIR__ . '/../Views/' . $view . '.php';
	require __DIR__ . '/../Views/layout/footer.php';

  }

  public static function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
  }
}

