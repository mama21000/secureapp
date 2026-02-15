<?php use App\Core\Security; ?>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body text-center py-5">
        <h3 class="mb-2">SecureApp</h3>
        <p class="text-muted mb-4">
          Secure profiles + transfers + activity logging.
        </p>

        <?php if (!empty($loggedIn)): ?>
          <div class="alert alert-success d-inline-block mb-4">
            Logged in as <strong><?= Security::e((string)$username) ?></strong>
          </div>

          <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a class="btn btn-primary" href="/profile/edit">My Profile</a>
            <a class="btn btn-outline-primary" href="/users/search">Search Users</a>
            <a class="btn btn-outline-primary" href="/transfer">Transfer</a>
            <a class="btn btn-outline-secondary" href="/history">History</a>
          </div>
        <?php else: ?>
          <p class="mb-4">Please login or register to continue.</p>
          <div class="d-flex justify-content-center gap-2">
            <a class="btn btn-primary" href="/login">Login</a>
            <a class="btn btn-outline-primary" href="/register">Register</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

