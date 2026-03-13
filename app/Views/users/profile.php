<?php 
use App\Core\Security; 
use App\Core\Auth; 

// Retrieve the nonce for CSP
$nonce = $_SESSION['csp_nonce'] ?? '';
?>

<style nonce="<?= $nonce ?>">
  .profile-card {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
  }
  
  /* Text Styles */
  .text-id { font-size: .85rem; }
  .text-joined { font-size: .82rem; }
  .text-username { font-size: .95rem; font-weight: 600; margin-top: 0.5rem; }
  .section-label {
    font-size: .8rem;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #9ca3af;
    font-weight: 600;
    margin-bottom: 0.5rem;
  }
  .detail-row { font-size: .88rem; margin-bottom: 0.5rem; }
  .bio-text {
    white-space: pre-wrap;
    font-size: .9rem;
    line-height: 1.7;
    color: #374151;
  }
  
  /* Avatar Styles */
  .avatar-lg {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e2e8f0;
  }
  .avatar-initials-lg {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: #4f46e5;
    color: #fff;
    font-size: 2rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    border: 3px solid #e2e8f0;
  }
  
  /* Account Badge */
  .account-badge {
    font-size: .82rem;
    border-radius: 8px;
  }
  .receiver-id { font-size: .78rem; }
</style>

<div class="card shadow-sm profile-card">
  <div class="card-body p-4">

    <!-- Top row: username + edit button -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h5 class="fw-bold mb-1">
          <?= Security::e((string)$u['username']) ?>
          <span class="text-muted fw-normal text-id">#<?= (int)$u['id'] ?></span>
        </h5>
        <div class="text-muted text-joined">
          Joined: <?= Security::e((string)$u['created_at']) ?>
        </div>
      </div>
      <?php if ((int)Auth::userId() === (int)$u['id']): ?>
        <a class="btn btn-sm btn-outline-primary" href="/profile/edit">Edit Profile</a>
      <?php endif; ?>
    </div>

    <hr class="my-3 opacity-10">

    <div class="row g-3">

      <!-- Left: Avatar + Details -->
      <div class="col-md-4">
        <div class="border rounded-3 p-3 bg-white h-100">

          <!-- Avatar — large display -->
          <div class="text-center mb-3">
            <?php
              $hasAvatar = !empty($u['avatar_path']);
              $avatarUrl = $hasAvatar ? '/avatar?id=' . (int)$u['id'] : null;
              $initial   = strtoupper(mb_substr((string)$u['username'], 0, 1));
              $safeInitial = Security::e($initial);
            ?>

            <?php if ($avatarUrl !== null): ?>
              <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>"
                   alt="<?= Security::e((string)$u['username']) ?>"
                   id="profileAvatarImg"
                   class="avatar-lg">
              
              <div id="profileAvatarFallback" 
                   class="avatar-initials-lg" 
                   style="display:none;">
                <?= $safeInitial ?>
              </div>

            <?php else: ?>
              <!-- No avatar — show initials placeholder -->
              <div class="avatar-initials-lg">
                <?= $safeInitial ?>
              </div>
            <?php endif; ?>

            <div class="text-username">
              <?= Security::e((string)$u['username']) ?>
            </div>
          </div>

          <div class="section-label">
            Details
          </div>

          <div class="detail-row">
            <span class="text-muted">Full name:</span>
            <span class="ms-1 fw-medium">
              <?= Security::e((string)($u['full_name'] ?? '')) !== ''
                  ? Security::e((string)($u['full_name'] ?? ''))
                  : '<span class="text-muted fst-italic">Not set</span>' ?>
            </span>
          </div>

          <div class="detail-row mb-3">
            <span class="text-muted">Phone:</span>
            <span class="ms-1 fw-medium">
              <?= Security::e((string)($u['phone'] ?? '')) !== ''
                  ? Security::e((string)($u['phone'] ?? ''))
                  : '<span class="text-muted fst-italic">Not set</span>' ?>
            </span>
          </div>

          <?php if ((int)Auth::userId() === (int)$u['id']): ?>
            <div class="alert alert-info mb-0 py-2 px-3 account-badge">
              This is your account.
            </div>
          <?php else: ?>
            <div class="mt-2">
              <a class="btn btn-sm btn-primary w-100" href="/transfer?receiver_id=<?= (int)$u['id'] ?>">Send Money</a>
              <div class="text-muted mt-2 text-center receiver-id">
                Receiver ID: <strong>#<?= (int)$u['id'] ?></strong>
              </div>
            </div>
          <?php endif; ?>

        </div>
      </div>

      <div class="col-md-8">
        <div class="border rounded-3 p-3 bg-white h-100">
          <div class="section-label">
            Bio
          </div>
          <?php $bio = trim((string)($u['bio'] ?? '')); ?>
          <?php if ($bio === ''): ?>
            <p class="text-muted fst-italic mb-0 bio-text">No bio provided.</p>
          <?php else: ?>
            <p class="mb-0 bio-text"><?= Security::e($bio) ?></p>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<script nonce="<?= $nonce ?>">
  document.addEventListener("DOMContentLoaded", function() {
    var img = document.getElementById('profileAvatarImg');
    if (img) {
      img.onerror = function() {
        this.style.display = 'none'; // Hide broken image
        var fallback = document.getElementById('profileAvatarFallback');
        if (fallback) fallback.style.display = 'flex'; // Show initials
      };
    }
  });
</script>
