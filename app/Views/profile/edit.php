<?php use App\Core\Security; use App\Core\CSRF; ?>
<div class="row justify-content-center">
  <div class="col-lg-9">
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
            <div class="border rounded p-3 bg-white">
              <div class="fw-semibold mb-2">Avatar</div>

              <div class="mb-3">
                <?php if (!empty($p['avatar_path'])): ?>
                  <img alt="avatar" class="rounded border w-100"
                       style="aspect-ratio: 1 / 1; object-fit: cover;"
                       src="/avatar?id=<?= (int)$p['user_id'] ?>">
                <?php else: ?>
                  <div class="text-muted">No avatar uploaded.</div>
                <?php endif; ?>
              </div>

              <form method="post" action="/profile/avatar" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= Security::e(CSRF::token()) ?>">
                <div class="mb-2">
                  <label class="form-label">Upload new avatar</label>
                  <input class="form-control" type="file" name="avatar"
                         accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                  <div class="form-text">JPG/PNG/WebP, max 1MB.</div>
                </div>
                <button class="btn btn-outline-primary w-100">Upload</button>
              </form>
            </div>
          </div>

          <div class="col-md-8">
            <form method="post" action="/profile/update" class="border rounded p-3 bg-white">
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

                <div class="col-12 d-flex gap-2 flex-wrap">
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

