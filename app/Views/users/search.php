<?php use App\Core\Security; ?>
<div class="card shadow-sm">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h5 class="mb-0">Search Users</h5>
      <span class="text-muted small">Find users by username or user ID</span>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= Security::e((string)$error) ?></div>
    <?php endif; ?>

    <form method="get" action="/users/search" class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label">Search by</label>
        <select class="form-select" name="mode">
          <option value="username" <?= (($mode ?? 'username') === 'username') ? 'selected' : '' ?>>Username</option>
          <option value="id" <?= (($mode ?? 'username') === 'id') ? 'selected' : '' ?>>User ID</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Query</label>
        <input class="form-control" name="q" value="<?= Security::e((string)($q ?? '')) ?>" required>
      </div>
      <div class="col-md-3">
        <button class="btn btn-primary w-100">Search</button>
      </div>
    </form>

    <hr>

    <?php if (isset($results)): ?>
      <?php if (empty($results)): ?>
        <p class="mb-0">No users found.</p>
      <?php else: ?>
        <div class="list-group">
          <?php foreach ($results as $r): ?>
            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
               href="/users/profile?id=<?= (int)$r['id'] ?>">
              <span>
                <?= Security::e((string)$r['username']) ?>
                <span class="text-muted">(#<?= (int)$r['id'] ?>)</span>
              </span>
              <span class="badge text-bg-light">View</span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</div>

