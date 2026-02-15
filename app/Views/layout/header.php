<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Security;

// Optional flash helper (safe even if you don't use it everywhere)
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title><?= htmlspecialchars($title ?? 'SecureApp') ?></title>

  <!-- Bootstrap (self-hosted) -->
  <link rel="stylesheet" href="/assets/bootstrap.min.css">

  <!-- Optional custom styles -->
  <link rel="stylesheet" href="/assets/app.css">
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="/">SecureApp</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"
            aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">

        <?php if (Auth::check()): ?>
          <li class="nav-item me-lg-2">
            <span class="navbar-text text-light">
              Welcome, <strong><?= htmlspecialchars(Auth::username()) ?></strong>
            </span>
          </li>

          <li class="nav-item"><a class="nav-link" href="/profile/edit">My Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="/users/search">Search</a></li>
          <li class="nav-item"><a class="nav-link" href="/transfer">Transfer</a></li>
          <li class="nav-item"><a class="nav-link" href="/history">History</a></li>

          <li class="nav-item ms-lg-2">
  			<form method="POST" action="/logout" class="d-inline">
    			<input type="hidden" name="_csrf" value="<?= \App\Core\CSRF::token() ?>">
    			<button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
  			</form>
		  </li>


        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="/register">Register</a></li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<main class="container py-4">

<?php if (!empty($flashSuccess)): ?>
  <div class="alert alert-success shadow-sm"><?= Security::e((string)$flashSuccess) ?></div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
  <div class="alert alert-danger shadow-sm"><?= Security::e((string)$flashError) ?></div>
<?php endif; ?>

