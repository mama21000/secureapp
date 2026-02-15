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

          <button class="btn btn-primary w-100">Create account</button>

          <div class="text-center small text-muted">
            Already have an account? <a href="/login">Login</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

