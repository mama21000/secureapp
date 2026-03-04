<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Uploads;
use App\Models\ProfileModel;
use App\Core\FileLogger;

final class ProfileController
{
    public function edit(): void
    {
        Auth::requireLogin();
        $uid     = (int)Auth::userId();
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

        // Normalize empty → null
        $fullName = $fullName === '' ? null : $fullName;
        $phone    = $phone    === '' ? null : $phone;
        $bio      = trim($bio);
        $bio      = $bio      === '' ? null : $bio;

        // Validation
        if ($fullName !== null && mb_strlen($fullName) > 120) {
            FileLogger::warning("Profile update failed for user ID {$uid}: Full name too long.");
            $_SESSION['flash_error'] = 'Full name too long.';
            Response::redirect('/profile/edit');
            return;
        }
        if ($phone !== null && (mb_strlen($phone) > 30 || !preg_match('/^[0-9+\-()\s]{6,30}$/', $phone))) {
            FileLogger::warning("Profile update failed for user ID {$uid}: Invalid phone number '{$phone}'.");
            $_SESSION['flash_error'] = 'Invalid phone number.';
            Response::redirect('/profile/edit');
            return;
        }
        if ($bio !== null && mb_strlen($bio) > 10000) {
            FileLogger::warning("Profile update failed for user ID {$uid}: Bio too long.");
            $_SESSION['flash_error'] = 'Bio too long.';
            Response::redirect('/profile/edit');
            return;
        }

        ProfileModel::upsert($uid, $fullName, $phone, $bio);

        // ── Flash success then redirect (prevents re-submit on refresh) ──
        $_SESSION['flash_success'] = 'Profile updated successfully.';
        FileLogger::info("Profile updated successfully for user ID {$uid}");
        Response::redirect('/profile/edit');
    }

    public function uploadAvatar(): void
    {
        Auth::requireLogin();
        CSRF::verify();

        $uid = (int)Auth::userId();

        $res = Uploads::handleAvatarUpload($_FILES['avatar'] ?? []);
        if (!$res['ok']) {
            $_SESSION['flash_error'] = $res['error'] ?? 'Upload failed.';
            Response::redirect('/profile/edit');
            return;
        }

        ProfileModel::setAvatar($uid, (string)$res['path'], (string)$res['mime'], (int)$res['size']);

        $_SESSION['flash_success'] = 'Avatar updated successfully.';
        Response::redirect('/profile/edit');
    }

    /**
     * Serve avatar safely (no direct file access).
     * GET /avatar?id=123
     */
    public function avatar(): void
    {
        Auth::requireLogin();

        $idStr = (string)($_GET['id'] ?? '');
        if (!ctype_digit($idStr)) {
            http_response_code(400);
            echo "Bad request";
            return;
        }

        $profile = ProfileModel::get((int)$idStr);
        $rel     = (string)($profile['avatar_path'] ?? '');

        if ($rel === '') {
            http_response_code(404);
            return;
        }

        // Only allow expected prefix — prevents path traversal
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