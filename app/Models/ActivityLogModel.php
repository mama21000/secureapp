<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class ActivityLogModel
{
  public static function latest(int $limit = 200): array
  {
    $limit = max(1, min($limit, 500));
    $pdo = DB::pdo();
    $stmt = $pdo->query("
      SELECT id, webpage, username, ip, created_at
      FROM activity_logs
      ORDER BY created_at DESC
      LIMIT {$limit}
    ");
    return $stmt->fetchAll();
  }
}

