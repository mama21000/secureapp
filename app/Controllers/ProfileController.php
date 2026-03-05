<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Uploads;
use App\Core\Validator;
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
        $phone    = trim((string)($_POST['phone']     ?? ''));
        $bio      = (string)($_POST['bio']            ?? '');

        $fullName = $fullName === '' ? null : $fullName;
        $phone    = $phone    === '' ? null : $phone;
        $bio      = trim($bio);
        $bio      = $bio      === '' ? null : $bio;

        if ($fullName !== null) {
            if ($err = Validator::fullName($fullName)) {
                FileLogger::warning("Profile update failed for user ID {$uid}: {$err}");
                $_SESSION['flash_error'] = $err;
                Response::redirect('/profile/edit');
                return;
            }
        }

        if ($phone !== null) {
            if ($err = Validator::phone($phone)) {
                FileLogger::warning("Profile update failed for user ID {$uid}: {$err}");
                $_SESSION['flash_error'] = $err;
                Response::redirect('/profile/edit');
                return;
            }
        }

        if ($bio !== null) {
            if ($err = Validator::bio($bio)) {
                FileLogger::warning("Profile update failed for user ID {$uid}: {$err}");
                $_SESSION['flash_error'] = $err;
                Response::redirect('/profile/edit');
                return;
            }
        }

        ProfileModel::upsert($uid, $fullName, $phone, $bio);

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

        if ($rel === '') { http_response_code(404); return; }
        if (!str_starts_with($rel, 'avatars/')) { http_response_code(404); return; }

        $abs = __DIR__ . '/../../storage/uploads/' . $rel;
        if (!is_file($abs)) { http_response_code(404); return; }

        header('Content-Type: image/webp');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=3600');
        readfile($abs);
    }
}