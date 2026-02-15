<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Uploads;
use App\Models\ProfileModel;

final class ProfileController
{
  public function edit(): void
  {
    Auth::requireLogin();
    $uid = (int)Auth::userId();
    $profile = ProfileModel::get($uid);

    Response::view('profile/edit', ['p' => $profile]);
  }

  public function update(): void
  {
    Auth::requireLogin();
    CSRF::verify();

    $uid = (int)Auth::userId();

    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $phone    = trim((string)($_POST['phone'] ?? ''));
    $bio      = (string)($_POST['bio'] ?? '');

    // Normalize empty -> null
    $fullName = $fullName === '' ? null : $fullName;
    $phone    = $phone === '' ? null : $phone;
    $bio      = trim($bio);
    $bio      = $bio === '' ? null : $bio;

    // Validation (defensive)
    if ($fullName !== null && mb_strlen($fullName) > 120) {
      Response::view('profile/edit', ['p' => ProfileModel::get($uid), 'error' => 'Full name too long.']);
      return;
    }
    if ($phone !== null && (mb_strlen($phone) > 30 || !preg_match('/^[0-9+\-()\s]{6,30}$/', $phone))) {
      Response::view('profile/edit', ['p' => ProfileModel::get($uid), 'error' => 'Invalid phone.']);
      return;
    }
    if ($bio !== null && mb_strlen($bio) > 10000) {
      Response::view('profile/edit', ['p' => ProfileModel::get($uid), 'error' => 'Bio too long.']);
      return;
    }

    ProfileModel::upsert($uid, $fullName, $phone, $bio);
    Response::view('profile/edit', ['p' => ProfileModel::get($uid), 'success' => 'Profile updated.']);
  }

  public function uploadAvatar(): void
  {
    Auth::requireLogin();
    CSRF::verify();

    $uid = (int)Auth::userId();

    $res = Uploads::handleAvatarUpload($_FILES['avatar'] ?? []);
    if (!$res['ok']) {
      Response::view('profile/edit', ['p' => ProfileModel::get($uid), 'error' => $res['error'] ?? 'Upload error.']);
      return;
    }

    ProfileModel::setAvatar($uid, (string)$res['path'], (string)$res['mime'], (int)$res['size']);
    Response::view('profile/edit', ['p' => ProfileModel::get($uid), 'success' => 'Avatar updated.']);
  }

  /**
   * Serve avatar safely (no direct file access).
   * GET /avatar?id=123
   */
  public function avatar(): void
  {
    Auth::requireLogin(); // prevents public scraping; adjust if specs require public

    $idStr = (string)($_GET['id'] ?? '');
    if (!ctype_digit($idStr)) {
      http_response_code(400);
      echo "Bad request";
      return;
    }

    $profile = ProfileModel::get((int)$idStr);
    $rel = (string)($profile['avatar_path'] ?? '');

    if ($rel === '') {
      http_response_code(404);
      return;
    }

    // Only allow expected prefix
    if (!str_starts_with($rel, 'avatars/')) {
      http_response_code(404);
      return;
    }

    $abs = __DIR__ . '/../../storage/uploads/' . $rel;
    if (!is_file($abs)) {
      http_response_code(404);
      return;
    }

    header('Content-Type: image/webp');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, max-age=3600');

    readfile($abs);
  }
}

