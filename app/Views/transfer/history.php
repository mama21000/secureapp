<?php use App\Core\Security; use App\Core\Auth; ?>
<div class="card shadow-sm">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h5 class="mb-0">Transaction History</h5>
      <a class="btn btn-sm btn-outline-primary" href="/transfer">New Transfer</a>
    </div>

    <?php if (empty($items)): ?>
      <p class="mb-0">No transactions yet.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>When</th>
              <th>Type</th>
              <th>From</th>
              <th>To</th>
              <th class="text-end">Amount</th>
              <th>Comment</th>
            </tr>
          </thead>
          <tbody>
          <?php $me = (int)Auth::userId(); ?>
          <?php foreach ($items as $t): ?>
            <?php
              $type = (string)$t['type'];
              $badge = ($type === 'DEBIT') ? 'text-bg-danger' : 'text-bg-success';
            ?>
            <tr>
              <td><?= (int)$t['id'] ?></td>
              <td><?= Security::e((string)$t['created_at']) ?></td>
              <td><span class="badge <?= $badge ?>"><?= Security::e($type) ?></span></td>
              <td>
                <?php if ((int)$t['sender_id'] === $me): ?>
                  <strong>Me</strong>
                <?php else: ?>
                  #<?= (int)$t['sender_id'] ?>
                <?php endif; ?>
              </td>
              <td>
                <?php if ((int)$t['receiver_id'] === $me): ?>
                  <strong>Me</strong>
                <?php else: ?>
                  #<?= (int)$t['receiver_id'] ?>
                <?php endif; ?>
              </td>
              <td class="text-end"><?= (int)$t['amount'] ?></td>
              <td style="max-width: 320px;">
                <span class="text-break"><?= Security::e((string)($t['comment'] ?? '')) ?></span>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </div>
</div>

