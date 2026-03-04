<?php use App\Core\Security; use App\Core\CSRF; ?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="mb-3 text-center">Register</h5>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= Security::e((string)$error) ?></div>
        <?php endif; ?>

        <form method="post" action="/register" autocomplete="off" class="vstack gap-3">
          <input type="hidden" name="_csrf" value="<?= Security::e(CSRF::token()) ?>">

          <div class="form-floating">
            <input class="form-control" id="username" name="username" placeholder="Username" required autofocus>
            <label for="username">Username</label>
          </div>

          <div class="form-floating">
            <input class="form-control" id="email" type="email" name="email" placeholder="Email" required>
            <label for="email">Email</label>
          </div>

          <div>
            <div class="form-floating">
              <input class="form-control" id="password" type="password" name="password" placeholder="Password" required minlength="10">
              <label for="password">Password</label>
            </div>
            <div class="form-text">Minimum 10 characters.</div>
          </div>

          <!-- Confirm Password -->
          <div class="form-floating">
            <input class="form-control" id="confirm_password" type="password" name="confirm_password" placeholder="Confirm Password" required minlength="10" oninput="checkPasswords()">
            <label for="confirm_password">Confirm Password</label>
          </div>
          <div id="password-error" class="text-danger small" style="display:none;">Passwords do not match.</div>

          <script>
            function checkPasswords() {
              const p = document.getElementById('password').value;
              const cp = document.getElementById('confirm_password').value;
              const err = document.getElementById('password-error');
              const btn = document.querySelector('.btn-primary');
              if (cp && p !== cp) {
                err.style.display = 'block';
                btn.disabled = true;
              } else {
                err.style.display = 'none';
                btn.disabled = false;
              }
            }
          </script>
        

          <button class="btn btn-primary w-100">Create account</button>

          <div class="text-center small text-muted">
            Already have an account? <a href="/login">Login</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>