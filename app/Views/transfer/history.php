<?php 
use App\Core\Security; 
use App\Core\Auth; 

// Retrieve the nonce for CSP
$nonce = $_SESSION['csp_nonce'] ?? '';
?>

<style nonce="<?= $nonce ?>">
  .history-card {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
  }
  
  /* Table Header */
  .tx-head {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #6b7280;
    padding: 10px 12px;
    white-space: nowrap;
  }
  
  /* Table Cells */
  .tx-cell {
    padding: 10px 12px;
    vertical-align: middle;
  }
  .tx-id {
    color: #9ca3af;
    font-size: .82rem;
  }
  .tx-date {
    font-size: .85rem;
    white-space: nowrap;
    color: #374151;
  }
  .tx-user {
    font-size: .85rem;
    color: #374151;
  }

  /* Badges */
  .badge-base {
    font-size: .72rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 50rem; /* rounded-pill */
  }
  .badge-sent {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
  }
  .badge-received {
    background: #dcfce7;
    color: #16a34a;
    border: 1px solid #bbf7d0;
  }
  .badge-you {
    background: #eef2ff;
    color: #4f46e5;
    font-size: .75rem;
    font-weight: 600;
    padding: 4px 9px;
    border-radius: 6px;
  }

  /* Amounts */
  .amount-base {
    font-weight: 700;
    font-size: .9rem;
  }
  .amount-sent { color: #dc2626; }
  .amount-received { color: #16a34a; }

  /* Comments */
  .comment-text {
    font-size: .82rem;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 220px;
  }
  .comment-empty {
    color: #d1d5db;
    font-size: .8rem;
  }

  /* Empty State */
  .empty-icon { font-size: 2.5rem; line-height: 1; }
  .empty-title { color: #374151; font-weight: 600; margin-top: 1rem; margin-bottom: 0.25rem; }
</style>

<div class="card shadow-sm history-card">
  <div class="card-body p-4">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <div>
        <h5 class="fw-bold mb-1">Transaction History</h5>
        <p class="text-muted mb-0 small">All your sent &amp; received transfers</p>
      </div>
      <a class="btn btn-primary btn-sm px-3" href="/transfer">+ New Transfer</a>
    </div>

    <?php if (empty($items)): ?>

      <!-- Empty State -->
      <div class="text-center py-5 text-muted">
        <div class="empty-icon">📭</div>
        <p class="empty-title">No records found</p>
        <p class="small mb-3">Your transaction history will appear here.</p>
        <a href="/transfer" class="btn btn-primary btn-sm px-4">Make your first transfer</a>
      </div>

    <?php else: ?>

      <div class="table-responsive">
        <table class="table table-hover table-striped table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="tx-head">ID</th>
              <th class="tx-head">Date &amp; Time</th>
              <th class="tx-head">Type</th>
              <th class="tx-head">From</th>
              <th class="tx-head">To</th>
              <th class="tx-head text-end">Amount (₹)</th>
              <th class="tx-head">Comment</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $me = (int)Auth::userId();
              foreach ($items as $t):
                $isSent        = (int)$t['sender_id'] === $me;
                $senderLabel   = Security::e((string)($t['sender_username']   ?? '#'.(int)$t['sender_id']));
                $receiverLabel = Security::e((string)($t['receiver_username'] ?? '#'.(int)$t['receiver_id']));
                $comment       = trim((string)($t['comment'] ?? ''));
            ?>
            <tr>

              <td class="tx-cell tx-id">
                #<?= (int)$t['id'] ?>
              </td>

              <td class="tx-cell tx-date">
                <?= Security::e((string)$t['created_at']) ?>
              </td>

              <td class="tx-cell">
                <?php if ($isSent): ?>
                  <span class="badge badge-base badge-sent">
                    ↑ Sent
                  </span>
                <?php else: ?>
                  <span class="badge badge-base badge-received">
                    ↓ Received
                  </span>
                <?php endif; ?>
              </td>

              <td class="tx-cell">
                <?php if ((int)$t['sender_id'] === $me): ?>
                  <span class="badge badge-you">You</span>
                <?php else: ?>
                  <span class="tx-user"><?= $senderLabel ?></span>
                <?php endif; ?>
              </td>

              <td class="tx-cell">
                <?php if ((int)$t['receiver_id'] === $me): ?>
                  <span class="badge badge-you">You</span>
                <?php else: ?>
                  <span class="tx-user"><?= $receiverLabel ?></span>
                <?php endif; ?>
              </td>

              <td class="tx-cell text-end">
                <span class="amount-base <?= $isSent ? 'amount-sent' : 'amount-received' ?>">
                  <?= $isSent ? '−' : '+' ?>₹<?= number_format((int)$t['amount']) ?>
                </span>
              </td>

              <td class="tx-cell">
                <?php if ($comment !== ''): ?>
                  <span class="text-muted fst-italic comment-text"
                        title="<?= htmlspecialchars($comment, ENT_QUOTES, 'UTF-8') ?>">
                    "<?= Security::e(mb_strimwidth($comment, 0, 40, '…')) ?>"
                  </span>
                <?php else: ?>
                  <span class="comment-empty">—</span>
                <?php endif; ?>
              </td>

            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <?php endif; ?>

  </div>
</div>
