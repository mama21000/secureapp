<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class UserModel
{
  public static function findById(int $id): ?array
  {
    if ($id <= 0) return null;

    $pdo = DB::pdo();
    $stmt = $pdo->prepare("
      SELECT u.id, u.username, u.email, u.balance, u.created_at,
             p.full_name, p.phone, p.bio, p.avatar_path, p.updated_at AS profile_updated_at
      FROM users u
      LEFT JOIN profiles p ON p.user_id = u.id
      WHERE u.id = ?
      LIMIT 1
    ");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
  }

  public static function findByUsernameExact(string $username): ?array
  {
    $username = trim($username);
    if ($username === '') return null;

    $pdo = DB::pdo();
    $stmt = $pdo->prepare("SELECT id, username, email, balance, created_at FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $row = $stmt->fetch();
    return $row ?: null;
  }

  /**
   * Safe username search (partial match).
   * Enforces a minimum length to reduce enumeration / brute scanning.
   */
  public static function searchByUsername(string $q, int $limit = 20): array
  {
    $q = trim($q);
    if (mb_strlen($q) < 2) return []; // reduces mass enumeration

    $limit = max(1, min($limit, 50));

    // Escape wildcard chars to avoid attacker controlling %/_ patterns too freely
    // We'll search: username LIKE %q% ESCAPE '\'
    $q = self::escapeLike($q);

    $pdo = DB::pdo();
    $stmt = $pdo->prepare("
      SELECT id, username
      FROM users
      WHERE username LIKE CONCAT('%', ?, '%') ESCAPE '\\\\'
      ORDER BY username ASC
      LIMIT {$limit}
    ");
    $stmt->execute([$q]);
    return $stmt->fetchAll();
  }

  private static function escapeLike(string $s): string
  {
    // Escape LIKE wildcards and backslash
    $s = str_replace('\\', '\\\\', $s);
    $s = str_replace('%', '\%', $s);
    $s = str_replace('_', '\_', $s);
    return $s;
  }
}

