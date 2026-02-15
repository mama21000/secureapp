<?php
declare(strict_types=1);

namespace App\Core;

final class Uploads
{
  // 1 MB max (adjust if allowed)
  public const MAX_AVATAR_BYTES = 1048576;

  // Store outside web root:
  public static function avatarDir(): string
  {
    return __DIR__ . '/../../storage/uploads/avatars';
  }

  /**
   * Validate & re-encode uploaded image to WebP.
   * Returns array{ok:bool, error?:string, path?:string, mime?:string, size?:int}
   */
  public static function handleAvatarUpload(array $file): array
  {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
      return ['ok' => false, 'error' => 'Upload failed.'];
    }
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
      return ['ok' => false, 'error' => 'Invalid upload.'];
    }
    if (!isset($file['size']) || (int)$file['size'] <= 0) {
      return ['ok' => false, 'error' => 'Empty file.'];
    }
    if ((int)$file['size'] > self::MAX_AVATAR_BYTES) {
      return ['ok' => false, 'error' => 'Avatar too large (max 1MB).'];
    }

    $tmp = (string)$file['tmp_name'];

    // MIME sniffing using finfo (trust this, not client headers)
    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp) ?: '';

    $allowed = [
      'image/jpeg',
      'image/png',
      'image/webp',
    ];
    if (!in_array($mime, $allowed, true)) {
      return ['ok' => false, 'error' => 'Only JPG/PNG/WebP allowed.'];
    }

    // Load image safely
    $img = null;
    if ($mime === 'image/jpeg') $img = @imagecreatefromjpeg($tmp);
    if ($mime === 'image/png')  $img = @imagecreatefrompng($tmp);
    if ($mime === 'image/webp') $img = @imagecreatefromwebp($tmp);

    if (!$img) {
      return ['ok' => false, 'error' => 'Invalid image data.'];
    }

    // Basic dimension sanity (prevents huge memory bombs)
    $w = imagesx($img);
    $h = imagesy($img);
    if ($w <= 0 || $h <= 0 || $w > 2000 || $h > 2000) {
      imagedestroy($img);
      return ['ok' => false, 'error' => 'Invalid image dimensions.'];
    }

    // Re-encode to WebP (strips metadata & payload tricks)
    $dir = self::avatarDir();
    if (!is_dir($dir)) {
      @mkdir($dir, 0750, true);
    }

    $name = bin2hex(random_bytes(16)) . '.webp';
    $absPath = $dir . '/' . $name;

    // Quality 80 is fine
    $ok = @imagewebp($img, $absPath, 80);
    imagedestroy($img);

    if (!$ok || !is_file($absPath)) {
      return ['ok' => false, 'error' => 'Failed to process image.'];
    }

    // Set restrictive permissions
    @chmod($absPath, 0640);

    $size = (int)filesize($absPath);

    // relative path stored in DB (not attacker-controlled)
    $relative = 'avatars/' . $name;

    return ['ok' => true, 'path' => $relative, 'mime' => 'image/webp', 'size' => $size];
  }
}

