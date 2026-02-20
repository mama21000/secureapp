<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Security;
use App\Core\CSRF;
use App\Models\ProfileModel;

// ── Flash messages — read once then clear from session ──────────────────
$flashSuccess = isset($_SESSION['flash_success']) ? (string)$_SESSION['flash_success'] : null;
$flashError   = isset($_SESSION['flash_error'])   ? (string)$_SESSION['flash_error']   : null;
$flashInfo    = isset($_SESSION['flash_info'])     ? (string)$_SESSION['flash_info']    : null;
unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['flash_info']);

// ── Avatar for navbar — only fetched when logged in ─────────────────────
// We serve via /avatar?id=X route (never expose raw storage path to browser)
// Falls back to initials circle in CSS if image fails to load
$navAvatarUrl = null;
$navInitial   = '';
if (Auth::check()) {
    $navUserId    = (int)Auth::userId();
    $navProfile   = ProfileModel::get($navUserId);
    $navInitial   = strtoupper(mb_substr((string)Auth::username(), 0, 1));
    // Only set avatar URL if a path is actually stored — avoids 404 on every request
    if (!empty($navProfile['avatar_path'])) {
        $navAvatarUrl = '/avatar?id=' . $navUserId;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="referrer" content="strict-origin-when-cross-origin">

  <title><?= htmlspecialchars((string)($title ?? 'SecureApp'), ENT_QUOTES, 'UTF-8') ?></title>

  <link rel="stylesheet" href="/assets/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/app.css">

  <style>
    /* ── Flash alerts ── */
    .flash-alert {
      border-left: 4px solid transparent;
      border-radius: 8px;
      padding: 12px 16px;
      font-size: .9rem;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,.07);
    }
    .flash-alert.alert-success { background:#f0fdf4; border-left-color:#16a34a; color:#14532d; }
    .flash-alert.alert-danger  { background:#fef2f2; border-left-color:#dc2626; color:#7f1d1d; }
    .flash-alert.alert-info    { background:#eff6ff; border-left-color:#2563eb; color:#1e3a8a; }
    .flash-alert .flash-icon   { font-size:1.1rem; flex-shrink:0; }
    .flash-alert .flash-text   { flex:1; }
    .flash-alert .btn-close    { flex-shrink:0; opacity:.5; }
    .flash-alert .btn-close:hover { opacity:1; }

    /* ── Navbar avatar ── */
    .nav-avatar-img {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid rgba(255,255,255,.3);
      vertical-align: middle;
    }
    .nav-avatar-initials {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: #4f46e5;
      border: 2px solid rgba(255,255,255,.3);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: .72rem;
      font-weight: 700;
      color: #fff;
      vertical-align: middle;
      flex-shrink: 0;
    }
    .nav-user-link {
      display: flex;
      align-items: center;
      gap: 7px;
      text-decoration: none;
      color: rgba(255,255,255,.85);
      font-size: .88rem;
      font-weight: 500;
      padding: 4px 8px;
      border-radius: 20px;
      transition: background .15s;
    }
    .nav-user-link:hover {
      background: rgba(255,255,255,.1);
      color: #fff;
    }
    .nav-username {
      max-width: 100px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  </style>
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="/">SecureApp</a>

    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#nav"
            aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">

        <?php if (Auth::check()): ?>
          <?php
            $safeUsername = htmlspecialchars((string)Auth::username(), ENT_QUOTES, 'UTF-8');
            $safeInitial  = htmlspecialchars($navInitial, ENT_QUOTES, 'UTF-8');
          ?>

          <!-- Avatar + username → links to profile -->
          <li class="nav-item me-lg-1">
            <a href="/profile/edit" class="nav-user-link">
              <?php if ($navAvatarUrl !== null): ?>
                <!-- Avatar image — onerror swaps to initials circle -->
                <img src="<?= htmlspecialchars($navAvatarUrl, ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= $safeUsername ?>"
                     class="nav-avatar-img"
                     id="navAvatarImg"
                     onerror="
                       this.style.display='none';
                       var el=document.getElementById('navAvatarInitials');
                       if(el) el.style.display='inline-flex';
                     ">
                <span class="nav-avatar-initials"
                      id="navAvatarInitials"
                      style="display:none;">
                  <?= $safeInitial ?>
                </span>
              <?php else: ?>
                <!-- No avatar stored — show initials circle directly -->
                <span class="nav-avatar-initials"><?= $safeInitial ?></span>
              <?php endif; ?>

              <span class="nav-username"><?= $safeUsername ?></span>
            </a>
          </li>

          <!-- <li class="nav-item"><a class="nav-link" href="/profile/edit">My Profile</a></li> -->
          <li class="nav-item"><a class="nav-link" href="/users/search">Search</a></li>
          <li class="nav-item"><a class="nav-link" href="/transfer">Transfer</a></li>
          <li class="nav-item"><a class="nav-link" href="/history">History</a></li>

          <li class="nav-item ms-lg-2">
            <form method="POST" action="/logout" class="d-inline">
              <input type="hidden" name="_csrf"
                     value="<?= htmlspecialchars(CSRF::token(), ENT_QUOTES, 'UTF-8') ?>">
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

  <!-- ── Flash Messages ── -->
  <?php if ($flashSuccess !== null || $flashError !== null || $flashInfo !== null): ?>
    <div class="mb-4">

      <?php if ($flashSuccess !== null): ?>
        <div class="flash-alert alert alert-success alert-dismissible fade show" role="alert">
          <span class="flash-icon">✅</span>
          <span class="flash-text"><?= Security::e($flashSuccess) ?></span>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
        </div>
      <?php endif; ?>

      <?php if ($flashError !== null): ?>
        <div class="flash-alert alert alert-danger alert-dismissible fade show" role="alert">
          <span class="flash-icon">❌</span>
          <span class="flash-text"><?= Security::e($flashError) ?></span>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
        </div>
      <?php endif; ?>

      <?php if ($flashInfo !== null): ?>
        <div class="flash-alert alert alert-info alert-dismissible fade show" role="alert">
          <span class="flash-icon">ℹ️</span>
          <span class="flash-text"><?= Security::e($flashInfo) ?></span>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
        </div>
      <?php endif; ?>

    </div>
  <?php endif; ?>