<?php use App\Core\Security; use App\Core\CSRF; ?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="mb-3 text-center">Login</h5>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= Security::e((string)$error) ?></div>
        <?php endif; ?>

        <form method="post" action="/login" autocomplete="off" class="vstack gap-3">
          <input type="hidden" name="_csrf" value="<?= Security::e(CSRF::token()) ?>">

          <div class="form-floating">
            <input class="form-control" id="username" name="username" placeholder="Username" required autofocus>
            <label for="username">Username</label>
          </div>

          <div class="form-floating">
            <input class="form-control" id="password" type="password" name="password" placeholder="Password" required>
            <label for="password">Password</label>
          </div>

          <button class="btn btn-primary w-100">Login</button>

          <div class="text-center small text-muted">
            No account? <a href="/register">Register</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

