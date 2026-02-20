<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Models\TransferModel;

final class TransferController
{
    public function show(): void
    {
        Auth::requireLogin();
        Response::view('transfer/form');
    }

    public function submit(): void
    {
        Auth::requireLogin();
        CSRF::verify();

        $senderId = Auth::userId();
        if ($senderId === null) {
            Response::redirect('/login');
            return;
        }

        $receiverId = (int)($_POST['receiver_id'] ?? 0);
        $amount     = (int)($_POST['amount'] ?? 0);
        $comment    = isset($_POST['comment']) ? (string)$_POST['comment'] : null;

        $res = TransferModel::transfer((int)$senderId, $receiverId, $amount, $comment);

        if (!$res['ok']) {
            // ── Flash error then redirect back to form ──
            $_SESSION['flash_error'] = $res['error'] ?? 'Transfer failed.';
            Response::redirect('/transfer');
            return;
        }

        // ── Flash success then redirect (prevents double-submit on refresh) ──
        $_SESSION['flash_success'] = 'Transfer successful! Transaction #' . ($res['tx_id'] ?? '');
        Response::redirect('/transfer');
    }

    public function history(): void
    {
        Auth::requireLogin();
        $uid = Auth::userId();
        if ($uid === null) {
            Response::redirect('/login');
            return;
        }

        $items = TransferModel::history((int)$uid, 50);
        Response::view('transfer/history', ['items' => $items]);
    }
}