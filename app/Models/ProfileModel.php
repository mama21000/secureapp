<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class ProfileModel
{
  public static function get(int $userId): array
  {
    $pdo = DB::pdo();
    $stmt = $pdo->prepare("
      SELECT user_id, full_name, phone, bio, avatar_path, avatar_mime, avatar_size, updated_at
      FROM profiles
      WHERE user_id = ?
      LIMIT 1
    ");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row ?: [
      'user_id' => $userId,
      'full_name' => null,
      'phone' => null,
      'bio' => null,
      'avatar_path' => null,
      'avatar_mime' => null,
      'avatar_size' => null,
      'updated_at' => null,
    ];
  }

  public static function upsert(int $userId, ?string $fullName, ?string $phone, ?string $bio): void
  {
    $pdo = DB::pdo();
    $stmt = $pdo->prepare("
      INSERT INTO profiles (user_id, full_name, phone, bio)
      VALUES (?, ?, ?, ?)
      ON DUPLICATE KEY UPDATE
        full_name = VALUES(full_name),
        phone = VALUES(phone),
        bio = VALUES(bio)
    ");
    $stmt->execute([$userId, $fullName, $phone, $bio]);
  }

  public static function setAvatar(int $userId, string $relativePath, string $mime, int $size): void
  {
    $pdo = DB::pdo();
    $stmt = $pdo->prepare("
      INSERT INTO profiles (user_id, avatar_path, avatar_mime, avatar_size)
      VALUES (?, ?, ?, ?)
      ON DUPLICATE KEY UPDATE
        avatar_path = VALUES(avatar_path),
        avatar_mime = VALUES(avatar_mime),
        avatar_size = VALUES(avatar_size)
    ");
    $stmt->execute([$userId, $relativePath, $mime, $size]);
  }
}

