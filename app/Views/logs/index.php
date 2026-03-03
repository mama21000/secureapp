<?php 
use App\Core\Security; 

// Retrieve the nonce for CSP
$nonce = $_SESSION['csp_nonce'] ?? '';
?>

<style nonce="<?= $nonce ?>">
  .log-card {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
  }
  .log-table-head {
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #6b7280;
    padding: 10px 12px;
    white-space: nowrap;
  }
  .log-cell {
    padding: 8px 12px;
    font-size: .85rem;
    color: #374151;
    vertical-align: middle;
  }
  .log-id { color: #9ca3af; font-size: .8rem; }
  .log-ip { font-family: monospace; color: #4b5563; }
  .log-user { font-weight: 600; color: #1e293b; }
</style>

<div class="card shadow-sm log-card">
  <div class="card-body p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0 fw-bold">Activity Logs</h5>
      <span class="badge bg-secondary">Latest 50</span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover table-striped table-sm mb-0">
        <thead class="table-light">
          <tr>
            <th class="log-table-head">ID</th>
            <th class="log-table-head">Time</th>
            <th class="log-table-head">User</th>
            <th class="log-table-head">IP Address</th>
            <th class="log-table-head">Page / Route</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($items)): ?>
            <tr>
              <td colspan="5" class="text-center py-4 text-muted small">No logs found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($items as $r): ?>
              <tr>
                <td class="log-cell log-id">#<?= (int)$r['id'] ?></td>
                <td class="log-cell text-nowrap"><?= Security::e((string)$r['created_at']) ?></td>
                <td class="log-cell log-user">
                  <?= Security::e((string)($r['username'] ?? 'guest')) ?>
                </td>
                <td class="log-cell log-ip"><?= Security::e((string)$r['ip']) ?></td>
                <td class="log-cell"><?= Security::e((string)$r['webpage']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>