<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;
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
        if (!isset($_POST['transfer_token']) || $_POST['transfer_token'] !== ($_SESSION['transfer_token'] ?? '')){
            http_response_code(403);
            $_SESSION['flash_error'] = 'Invalid or reused transfer token';
			Response::redirect('/transfer');
        }
        unset($_SESSION['transfer_token']);

        $senderId = Auth::userId();
        if ($senderId === null) {
            Response::redirect('/login');
            return;
        }

        $receiverIdRaw = $_POST['receiver_id'] ?? '';
        $amountRaw     = $_POST['amount']      ?? '';
        $comment       = isset($_POST['comment']) ? trim((string)$_POST['comment']) : null;

        if (!ctype_digit((string)$receiverIdRaw) || (int)$receiverIdRaw <= 0) {
            $_SESSION['flash_error'] = 'Please enter a valid recipient user ID.';
            Response::redirect('/transfer');
            return;
        }
        $receiverId = (int)$receiverIdRaw;

        if ($err = Validator::moneyAmount($amountRaw, 1, 100000)) {
            $_SESSION['flash_error'] = $err;
            Response::redirect('/transfer');
            return;
        }
        $amount = (int)$amountRaw;

        if ($comment !== null && $comment !== '') {
            if ($err = Validator::transferComment($comment)) {
                $_SESSION['flash_error'] = $err;
                Response::redirect('/transfer');
                return;
            }
        } else {
            $comment = null;
        }

        if ($receiverId === (int)$senderId) {
            $_SESSION['flash_error'] = 'You cannot transfer money to yourself.';
            Response::redirect('/transfer');
            return;
        }

        $res = TransferModel::transfer((int)$senderId, $receiverId, $amount, $comment);

        if (!$res['ok']) {
            $_SESSION['flash_error'] = $res['error'] ?? 'Transfer failed.';
            Response::redirect('/transfer');
            return;
        }

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
