<?php 
use App\Core\Security; 

// Retrieve the nonce for CSP
$nonce = $_SESSION['csp_nonce'] ?? '';
?>

<style nonce="<?= $nonce ?>">
  .search-card {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
  }
  .search-badge {
    font-weight: 500;
    font-size: 0.85rem;
  }
  .user-id-muted {
    font-size: 0.85rem;
    color: #6c757d;
  }
</style>

<div class="card shadow-sm search-card">
  <div class="card-body p-4">
    
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h5 class="mb-0 fw-bold">Search Users</h5>
      <span class="text-muted small">Find users by username or user ID</span>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= Security::e((string)$error) ?></div>
    <?php endif; ?>

    <form method="get" action="/users/search" class="row g-2 align-items-end" autocomplete="off">
      <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted">Search by</label>
        <select class="form-select" name="mode">
          <option value="username" <?= (($mode ?? 'username') === 'username') ? 'selected' : '' ?>>Username</option>
          <option value="id" <?= (($mode ?? 'username') === 'id') ? 'selected' : '' ?>>User ID</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold text-muted">Query</label>
        <input class="form-control" name="q" 
               value="<?= Security::e((string)($q ?? '')) ?>" 
               placeholder="Enter username or ID..."
               required>
      </div>
      <div class="col-md-3">
        <button class="btn btn-primary w-100">Search</button>
      </div>
    </form>

    <hr class="my-4 opacity-10">

    <?php if (isset($results)): ?>
      <?php if (empty($results)): ?>
        <div class="text-center text-muted py-3">
          <p class="mb-0">No users found matching your query.</p>
        </div>
      <?php else: ?>
        <div class="list-group list-group-flush">
          <?php foreach ($results as $r): ?>
            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-3 py-3 border rounded mb-2"
               href="/users/profile?id=<?= (int)$r['id'] ?>">
              <span>
                <span class="fw-semibold"><?= Security::e((string)$r['username']) ?></span>
                <span class="user-id-muted ms-1">(#<?= (int)$r['id'] ?>)</span>
              </span>
              <span class="badge bg-light text-dark border search-badge">View Profile</span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</div>