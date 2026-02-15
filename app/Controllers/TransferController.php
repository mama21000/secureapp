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
      Response::view('transfer/form', ['error' => $res['error'] ?? 'Error']);
      return;
    }

    Response::view('transfer/form', ['success' => 'Transfer successful!', 'tx_id' => $res['tx_id'] ?? null]);
  }

  public function history(): void
  {
    Auth::requireLogin();
    $uid = Auth::userId();
    if ($uid === null) { Response::redirect('/login'); return; }

    $items = TransferModel::history((int)$uid, 50);
    Response::view('transfer/history', ['items' => $items]);
  }
}

