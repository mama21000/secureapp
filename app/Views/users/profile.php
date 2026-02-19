<?php use App\Core\Security; use App\Core\Auth; ?>

<div class="card shadow-sm" style="border-radius:12px;border:1px solid #e2e8f0;">
  <div class="card-body p-4">

    <!-- Top row: username + edit button -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h5 class="fw-bold mb-1">
          <?= Security::e((string)$u['username']) ?>
          <span class="text-muted fw-normal" style="font-size:.85rem;">#<?= (int)$u['id'] ?></span>
        </h5>
        <div class="text-muted" style="font-size:.82rem;">
          Joined: <?= Security::e((string)$u['created_at']) ?>
        </div>
      </div>
      <?php if ((int)Auth::userId() === (int)$u['id']): ?>
        <a class="btn btn-sm btn-outline-primary" href="/profile/edit">Edit Profile</a>
      <?php endif; ?>
    </div>

    <hr class="my-3">

    <div class="row g-3">

      <!-- Left: Avatar + Details -->
      <div class="col-md-4">
        <div class="border rounded-3 p-3 bg-white h-100">

          <!-- Avatar — large display -->
          <div class="text-center mb-3">
            <?php
              $hasAvatar = !empty($u['avatar_path']);
              $avatarUrl = $hasAvatar
                ? '/avatar?id=' . (int)$u['id']
                : null;
              $initial   = strtoupper(mb_substr((string)$u['username'], 0, 1));
              $safeInitial = htmlspecialchars($initial, ENT_QUOTES, 'UTF-8');
            ?>

            <?php if ($avatarUrl !== null): ?>
              <!-- Real avatar image — falls back to initials on error -->
              <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>"
                   alt="<?= htmlspecialchars((string)$u['username'], ENT_QUOTES, 'UTF-8') ?>"
                   id="profileAvatarImg"
                   style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #e2e8f0;"
                   onerror="
                     this.style.display='none';
                     var el=document.getElementById('profileAvatarInitials');
                     if(el) el.style.display='flex';
                   ">
              <div id="profileAvatarInitials"
                   style="display:none;width:90px;height:90px;border-radius:50%;background:#4f46e5;
                          color:#fff;font-size:2rem;font-weight:700;align-items:center;
                          justify-content:center;margin:0 auto;border:3px solid #e2e8f0;">
                <?= $safeInitial ?>
              </div>
            <?php else: ?>
              <!-- No avatar — show initials placeholder -->
              <div style="width:90px;height:90px;border-radius:50%;background:#4f46e5;
                          color:#fff;font-size:2rem;font-weight:700;display:flex;
                          align-items:center;justify-content:center;
                          margin:0 auto;border:3px solid #e2e8f0;">
                <?= $safeInitial ?>
              </div>
            <?php endif; ?>

            <div class="fw-semibold mt-2" style="font-size:.95rem;">
              <?= Security::e((string)$u['username']) ?>
            </div>
          </div>

          <!-- Details -->
          <div class="fw-semibold mb-2" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;">
            Details
          </div>

          <div class="mb-2" style="font-size:.88rem;">
            <span class="text-muted">Full name:</span>
            <span class="ms-1">
              <?= Security::e((string)($u['full_name'] ?? '')) !== ''
                  ? Security::e((string)($u['full_name'] ?? ''))
                  : '<span class="text-muted fst-italic">Not set</span>' ?>
            </span>
          </div>

          <div class="mb-3" style="font-size:.88rem;">
            <span class="text-muted">Phone:</span>
            <span class="ms-1">
              <?= Security::e((string)($u['phone'] ?? '')) !== ''
                  ? Security::e((string)($u['phone'] ?? ''))
                  : '<span class="text-muted fst-italic">Not set</span>' ?>
            </span>
          </div>

          <?php if ((int)Auth::userId() === (int)$u['id']): ?>
            <div class="alert alert-info mb-0 py-2 px-3" style="font-size:.82rem;border-radius:8px;">
              This is your account.
            </div>
          <?php else: ?>
            <div class="mt-2">
              <a class="btn btn-sm btn-primary w-100" href="/transfer">Send Money</a>
              <div class="text-muted mt-2 text-center" style="font-size:.78rem;">
                Receiver ID: <strong>#<?= (int)$u['id'] ?></strong>
              </div>
            </div>
          <?php endif; ?>

        </div>
      </div>

      <!-- Right: Bio -->
      <div class="col-md-8">
        <div class="border rounded-3 p-3 bg-white h-100">
          <div class="fw-semibold mb-2" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;">
            Bio
          </div>
          <?php $bio = trim((string)($u['bio'] ?? '')); ?>
          <?php if ($bio === ''): ?>
            <p class="text-muted fst-italic mb-0" style="font-size:.9rem;">No bio provided.</p>
          <?php else: ?>
            <p class="mb-0" style="white-space:pre-wrap;font-size:.9rem;line-height:1.7;color:#374151;">
              <?= Security::e($bio) ?>
            </p>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>