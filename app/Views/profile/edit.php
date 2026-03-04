<?php 
use App\Core\Security; 
use App\Core\CSRF; 

$nonce = $_SESSION['csp_nonce'] ?? '';
?>

<style nonce="<?= $nonce ?>">
  .avatar-box {
    aspect-ratio: 1 / 1;
    object-fit: cover;
    width: 100%;
    border-radius: 0.375rem;
    border: 1px solid #dee2e6;
  }
  .avatar-placeholder {
    aspect-ratio: 1 / 1;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 700;
    color: #fff;
    background-color: #6c757d;
    border-radius: 0.375rem;
    border: 1px solid #dee2e6;
  }
</style>

<div class="row justify-content-center">
  <div class="col-lg-9">

    <div class="card shadow-sm mb-4 border-success">
      <div class="card-body">
        <div class="text-muted small mb-1">Current Balance</div>
        <div class="fs-3 fw-bold text-success">
          ₹<?= number_format((int)($p['balance'] ?? 0)) ?>
        </div>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">
        
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
          <h5 class="mb-0">Edit Profile</h5>
          <a class="btn btn-sm btn-outline-secondary" href="/users/profile?id=<?= (int)$p['user_id'] ?>">View My Profile</a>
        </div>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= Security::e((string)$error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
          <div class="alert alert-success"><?= Security::e((string)$success) ?></div>
        <?php endif; ?>

        <div class="row g-4">
          
          <div class="col-md-4">
            <div class="border rounded p-3 bg-white h-100">
              <div class="fw-semibold mb-2">Avatar</div>

              <div class="mb-3">
                <?php 
                  $dispName = $p['username'] ?? 'User';
                  $initial  = strtoupper(mb_substr((string)$dispName, 0, 1));
                ?>

                <?php if (!empty($p['avatar_path'])): ?>
                  <img alt="avatar" 
                       class="avatar-box"
                       src="/avatar?id=<?= (int)$p['user_id'] ?>"
                       id="editAvatarImg">
                  <div class="avatar-placeholder" id="editAvatarFallback" style="display:none;">
                    <?= Security::e($initial) ?>
                  </div>
                <?php else: ?>
                  <div class="avatar-placeholder">
                    <?= Security::e($initial) ?>
                  </div>
                <?php endif; ?>
              </div>

              <form method="post" action="/profile/avatar" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= Security::e(CSRF::token()) ?>">
                <div class="mb-2">
                  <label class="form-label small text-muted">Upload new avatar</label>
                  <input class="form-control form-control-sm" type="file" name="avatar"
                         accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                  <div class="form-text small">JPG/PNG/WebP, max 1MB.</div>
                </div>
                <button class="btn btn-outline-primary btn-sm w-100">Upload</button>
              </form>
            </div>
          </div>

          <div class="col-md-8">
            <form method="post" action="/profile/update" class="border rounded p-3 bg-white h-100">
              <input type="hidden" name="_csrf" value="<?= Security::e(CSRF::token()) ?>">

              <div class="row g-3">
                <div class="col-md-6">
                  <div class="form-floating">
                    <input class="form-control" id="full_name" name="full_name" maxlength="120"
                      placeholder="Full Name"
                      value="<?= Security::e((string)($p['full_name'] ?? '')) ?>">
                    <label for="full_name">Full Name</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input class="form-control" id="phone" name="phone" maxlength="30"
                      placeholder="Phone"
                      value="<?= Security::e((string)($p['phone'] ?? '')) ?>">
                    <label for="phone">Phone</label>
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label fw-semibold">Bio</label>
                  <textarea class="form-control" id="bio" name="bio" rows="6"><?= Security::e((string)($p['bio'] ?? '')) ?></textarea>
                  <div class="form-text">Max 10,000 characters.</div>
                </div>

                <div class="col-12 d-flex gap-2 flex-wrap pt-2">
                  <button class="btn btn-primary">Save Profile</button>
                  <a class="btn btn-outline-secondary" href="/users/profile?id=<?= (int)$p['user_id'] ?>">View Profile</a>
                </div>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script nonce="<?= $nonce ?>">
  document.addEventListener("DOMContentLoaded", function() {
    var img = document.getElementById('editAvatarImg');
    if (img) {
      img.onerror = function() {
        this.style.display = 'none';
        var fallback = document.getElementById('editAvatarFallback');
        if (fallback) fallback.style.display = 'flex';
      };
    }
  });
</script>