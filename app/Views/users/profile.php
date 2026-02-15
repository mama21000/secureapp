<?php use App\Core\Security; use App\Core\Auth; ?>
<div class="card shadow-sm">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
      <div>
        <h5 class="mb-1">
          Profile: <?= Security::e((string)$u['username']) ?>
          <span class="text-muted">(#<?= (int)$u['id'] ?>)</span>
        </h5>
        <div class="text-muted small">
          Joined: <?= Security::e((string)$u['created_at']) ?>
        </div>
      </div>

      <?php if ((int)Auth::userId() === (int)$u['id']): ?>
        <a class="btn btn-sm btn-outline-primary" href="/profile/edit">Edit Profile</a>
      <?php endif; ?>
    </div>

    <hr>

    <div class="row g-3">
      <div class="col-md-4">
        <div class="border rounded p-3 bg-white h-100">
          <div class="fw-semibold mb-2">Details</div>
          <div class="mb-1"><span class="text-muted">Full name:</span> <?= Security::e((string)($u['full_name'] ?? '')) ?></div>
          <div class="mb-1"><span class="text-muted">Phone:</span> <?= Security::e((string)($u['phone'] ?? '')) ?></div>

          <?php if ((int)Auth::userId() === (int)$u['id']): ?>
            <div class="mt-3 alert alert-info mb-0">
              This is your account.
            </div>
          <?php else: ?>
            <div class="mt-3">
              <a class="btn btn-sm btn-primary" href="/transfer">Send Money</a>
              <div class="small text-muted mt-1">
                Use receiver ID: <strong>#<?= (int)$u['id'] ?></strong>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-md-8">
        <div class="border rounded p-3 bg-white h-100">
          <div class="fw-semibold mb-2">Bio</div>
          <?php $bio = (string)($u['bio'] ?? ''); ?>
          <?php if ($bio === ''): ?>
            <p class="text-muted mb-0">No bio provided.</p>
          <?php else: ?>
            <p class="mb-0" style="white-space: pre-wrap;"><?= Security::e($bio) ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>

