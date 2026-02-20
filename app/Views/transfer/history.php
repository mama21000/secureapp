<?php use App\Core\Security; use App\Core\Auth; ?>

<div class="card shadow-sm" style="border-radius:12px;border:1px solid #e2e8f0;">
  <div class="card-body p-4">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <div>
        <h5 class="fw-bold mb-1">Transaction History</h5>
        <p class="text-muted mb-0" style="font-size:.85rem;">All your sent &amp; received transfers</p>
      </div>
      <a class="btn btn-primary btn-sm px-3" href="/transfer">+ New Transfer</a>
    </div>

    <?php if (empty($items)): ?>

      <!-- Empty State -->
      <div class="text-center py-5 text-muted">
        <div style="font-size:2.5rem;line-height:1;">📭</div>
        <p class="fw-semibold mt-3 mb-1" style="color:#374151;">No records found</p>
        <p class="small mb-3">Your transaction history will appear here.</p>
        <a href="/transfer" class="btn btn-primary btn-sm px-4">Make your first transfer</a>
      </div>

    <?php else: ?>

      <div class="table-responsive">
        <table class="table table-hover table-striped table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;padding:10px 12px;white-space:nowrap;">ID</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;padding:10px 12px;white-space:nowrap;">Date &amp; Time</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;padding:10px 12px;">Type</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;padding:10px 12px;">From</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;padding:10px 12px;">To</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;padding:10px 12px;text-align:right;">Amount (₹)</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;padding:10px 12px;">Comment</th>
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

              <!-- ID -->
              <td style="padding:10px 12px;color:#9ca3af;font-size:.82rem;">
                #<?= (int)$t['id'] ?>
              </td>

              <!-- Date -->
              <td style="padding:10px 12px;white-space:nowrap;font-size:.85rem;">
                <?= Security::e((string)$t['created_at']) ?>
              </td>

              <!-- Type badge -->
              <td style="padding:10px 12px;">
                <?php if ($isSent): ?>
                  <span class="badge rounded-pill"
                        style="background:#fee2e2;color:#dc2626;font-size:.72rem;font-weight:600;padding:4px 10px;border:1px solid #fecaca;">
                    ↑ Sent
                  </span>
                <?php else: ?>
                  <span class="badge rounded-pill"
                        style="background:#dcfce7;color:#16a34a;font-size:.72rem;font-weight:600;padding:4px 10px;border:1px solid #bbf7d0;">
                    ↓ Received
                  </span>
                <?php endif; ?>
              </td>

              <!-- From -->
              <td style="padding:10px 12px;">
                <?php if ((int)$t['sender_id'] === $me): ?>
                  <span class="badge"
                        style="background:#eef2ff;color:#4f46e5;font-size:.75rem;font-weight:600;padding:4px 9px;border-radius:6px;">
                    You
                  </span>
                <?php else: ?>
                  <span style="font-size:.85rem;color:#374151;"><?= $senderLabel ?></span>
                <?php endif; ?>
              </td>

              <!-- To -->
              <td style="padding:10px 12px;">
                <?php if ((int)$t['receiver_id'] === $me): ?>
                  <span class="badge"
                        style="background:#eef2ff;color:#4f46e5;font-size:.75rem;font-weight:600;padding:4px 9px;border-radius:6px;">
                    You
                  </span>
                <?php else: ?>
                  <span style="font-size:.85rem;color:#374151;"><?= $receiverLabel ?></span>
                <?php endif; ?>
              </td>

              <!-- Amount (right-aligned, colored) -->
              <td style="padding:10px 12px;text-align:right;white-space:nowrap;">
                <span style="font-weight:700;font-size:.9rem;color:<?= $isSent ? '#dc2626' : '#16a34a' ?>;">
                  <?= $isSent ? '−' : '+' ?>₹<?= number_format((int)$t['amount']) ?>
                </span>
              </td>

              <!-- Comment -->
              <td style="padding:10px 12px;max-width:220px;">
                <?php if ($comment !== ''): ?>
                  <span class="text-muted fst-italic"
                        style="font-size:.82rem;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                        title="<?= htmlspecialchars($comment, ENT_QUOTES, 'UTF-8') ?>">
                    "<?= Security::e(mb_strimwidth($comment, 0, 40, '…')) ?>"
                  </span>
                <?php else: ?>
                  <span style="color:#d1d5db;font-size:.8rem;">—</span>
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