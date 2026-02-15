<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;

final class TransferModel
{
  /**
   * Execute a money transfer safely (atomic + race-safe).
   *
   * @return array{ok:bool, error?:string, tx_id?:int}
   */
  public static function transfer(int $senderId, int $receiverId, int $amount, ?string $comment): array
  {
    if ($senderId <= 0 || $receiverId <= 0) return ['ok' => false, 'error' => 'Invalid user.'];
    if ($senderId === $receiverId) return ['ok' => false, 'error' => 'Invalid transfer.'];
    if ($amount <= 0) return ['ok' => false, 'error' => 'Invalid amount.'];
    if ($amount > 1000000) return ['ok' => false, 'error' => 'Amount too large.']; // sanity cap

    $comment = $comment !== null ? trim($comment) : null;
    if ($comment === '') $comment = null;
    if ($comment !== null && mb_strlen($comment) > 255) {
      return ['ok' => false, 'error' => 'Comment too long.'];
    }

    $pdo = DB::pdo();

    try {
      $pdo->beginTransaction();

      // Deadlock avoidance: lock rows in deterministic order
      $a = min($senderId, $receiverId);
      $b = max($senderId, $receiverId);

      // Lock both user rows
      $lock = $pdo->prepare("SELECT id, balance FROM users WHERE id IN (?, ?) FOR UPDATE");
      $lock->execute([$a, $b]);
      $rows = $lock->fetchAll(PDO::FETCH_ASSOC);

      if (count($rows) !== 2) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => 'Receiver not found.'];
      }

      // Map balances by id
      $balances = [];
      foreach ($rows as $r) $balances[(int)$r['id']] = (int)$r['balance'];

      $senderBal = $balances[$senderId] ?? null;
      $recvBal   = $balances[$receiverId] ?? null;

      if ($senderBal === null || $recvBal === null) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => 'Receiver not found.'];
      }

      if ($senderBal < $amount) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => 'Insufficient balance.'];
      }

      // Update balances
      $upd = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
      $upd->execute([$senderBal - $amount, $senderId]);
      $upd->execute([$recvBal + $amount, $receiverId]);

      // Insert transaction record
      $ins = $pdo->prepare("
        INSERT INTO transactions (sender_id, receiver_id, amount, comment)
        VALUES (?, ?, ?, ?)
      ");
      $ins->execute([$senderId, $receiverId, $amount, $comment]);

      $txId = (int)$pdo->lastInsertId();

      $pdo->commit();
      return ['ok' => true, 'tx_id' => $txId];

    } catch (\Throwable $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();

      // Don’t leak internal DB error details to attacker
      return ['ok' => false, 'error' => 'Transfer failed. Try again.'];
    }
  }

  /**
   * Transaction history for a user (sent + received).
   */
  public static function history(int $userId, int $limit = 50): array
  {
    $limit = max(1, min($limit, 200));

    $pdo = DB::pdo();
    $stmt = $pdo->prepare("
      SELECT
        t.id, t.sender_id, t.receiver_id, t.amount, t.comment, t.created_at,
        su.username AS sender_username,
        ru.username AS receiver_username
      FROM transactions t
      JOIN users su ON su.id = t.sender_id
      JOIN users ru ON ru.id = t.receiver_id
      WHERE t.sender_id = ? OR t.receiver_id = ?
      ORDER BY t.created_at DESC
      LIMIT {$limit}
    ");
    $stmt->execute([$userId, $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}

