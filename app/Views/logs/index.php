<?php use App\Core\Security; ?>
<div class="card">
  <div class="card-body">
    <h5 class="mb-3">Activity Logs (Latest)</h5>

    <div class="table-responsive">
      <table class="table table-sm table-striped">
        <thead>
          <tr>
            <th>ID</th><th>Time</th><th>User</th><th>IP</th><th>Page</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (($items ?? []) as $r): ?>
            <tr>
              <td><?= (int)$r['id'] ?></td>
              <td><?= Security::e((string)$r['created_at']) ?></td>
              <td><?= Security::e((string)($r['username'] ?? 'guest')) ?></td>
              <td><?= Security::e((string)$r['ip']) ?></td>
              <td><?= Security::e((string)$r['webpage']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

