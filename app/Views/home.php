<?php 
use App\Core\Security; 
use App\Core\Auth; 

// Retrieve the nonce generated in Security::init()
$nonce = $_SESSION['csp_nonce'] ?? '';
?>

<style nonce="<?= $nonce ?>">
  /* Balance Card */
  .balance-card {
    background: linear-gradient(135deg, #1a56db, #6366f1);
    border-radius: 14px;
    color: white;
  }
  .balance-label {
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .8px;
    font-weight: 600;
    color: rgba(255,255,255,0.5); /* text-white-50 equivalent */
  }
  .balance-value {
    font-size: 2rem;
    letter-spacing: -1px;
    font-weight: 700;
  }
  .account-label {
    font-size: .82rem;
    color: rgba(255,255,255,0.5);
  }

  /* Quick Link Cards (Replaces onmouseover logic) */
  .quick-link-card {
    height: 100%;
    border: 1px solid #e2e8f0 !important;
    border-radius: 12px;
    text-align: center;
    padding: 1rem 0.5rem;
    transition: all .18s ease;
    background: white;
  }
  /* The Hover Effect (Replaces JS) */
  .quick-link-card:hover {
    border-color: #6366f1 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(99,102,241,.15);
  }
  .quick-link-icon {
    font-size: 1.6rem;
    margin-bottom: 0.5rem;
  }
  .quick-link-text {
    font-size: .85rem;
    color: #1e293b;
    font-weight: 600;
  }

  /* Transaction Table */
  .tx-header {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #94a3b8;
    border-bottom: 2px solid #e2e8f0;
    padding: 8px 12px;
  }
  .tx-cell {
    padding: 11px 12px;
  }
  .tx-user-text {
    font-size: .88rem;
    color: #1e293b;
    font-weight: 600;
  }
  .tx-date {
    font-size: .82rem;
    color: #6c757d;
    white-space: nowrap;
  }
  .tx-comment {
    font-size: .82rem;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  /* Badges & Amounts */
  .badge-sent {
    background: #fee2e2;
    color: #dc2626;
    font-size: .75rem;
    font-weight: 600;
    padding: 5px 10px;
  }
  .badge-received {
    background: #dcfce7;
    color: #16a34a;
    font-size: .75rem;
    font-weight: 600;
    padding: 5px 10px;
  }
  .amount-sent { color: #dc2626; font-size: .95rem; font-weight: 700; }
  .amount-received { color: #16a34a; font-size: .95rem; font-weight: 700; }

  /* Logged Out Hero */
  .hero-card {
    border-radius: 16px;
    padding: 3rem;
  }
  .hero-icon { font-size: 2.8rem; }
  .hero-badges { font-size: .82rem; }
</style>

<?php if (!empty($loggedIn)): ?>
<!-- ════════════ LOGGED IN — DASHBOARD ════════════ -->

<!-- Welcome Row -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <div>
    <h4 class="fw-bold mb-1">
      Welcome back, <?= Security::e((string)$username) ?> 👋
    </h4>
    <p class="text-muted mb-0 small">
      Here's a summary of your account.
    </p>
  </div>
  <a href="/transfer" class="btn btn-primary px-4">+ New Transfer</a>
</div>

<!-- Row 1: Balance + Quick Links -->
<div class="row g-3 mb-4">

  <!-- Balance Card -->
  <div class="col-md-4">
    <div class="card h-100 border-0 balance-card">
      <div class="card-body p-4">
        <p class="mb-1 balance-label">
          Available Balance
        </p>
        <h2 class="mb-1 balance-value">
          ₹<?= number_format((int)($balance ?? 0)) ?>
        </h2>
        <p class="mb-0 account-label">
          Account: <strong class="text-white"><?= Security::e((string)$username) ?></strong>
        </p>
      </div>
    </div>
  </div>

  <!-- Quick Links Grid -->
  <div class="col-md-8">
    <div class="row g-3 h-100">

      <div class="col-6 col-sm-3">
          <a href="/users/profile?id=<?= (int)Auth::userId() ?>" class="text-decoration-none">
            <div class="quick-link-card">
            <div class="quick-link-icon">👤</div>
            <div class="quick-link-text">Profile</div>
          </div>
        </a>
      </div>

      <div class="col-6 col-sm-3">
        <a href="/users/search" class="text-decoration-none">
          <div class="quick-link-card">
            <div class="quick-link-icon">🔍</div>
            <div class="quick-link-text">Search</div>
          </div>
        </a>
      </div>

      <div class="col-6 col-sm-3">
        <a href="/transfer" class="text-decoration-none">
          <div class="quick-link-card">
            <div class="quick-link-icon">💸</div>
            <div class="quick-link-text">Transfer</div>
          </div>
        </a>
      </div>

      <div class="col-6 col-sm-3">
        <a href="/history" class="text-decoration-none">
          <div class="quick-link-card">
            <div class="quick-link-icon">📋</div>
            <div class="quick-link-text">History</div>
          </div>
        </a>
      </div>

    </div>
  </div>
</div>

<!-- Row 2: Recent Transactions -->
<div class="card border-0 shadow-sm" style="border-radius:14px;">
  <div class="card-body p-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h6 class="fw-bold mb-0">Recent Transactions</h6>
      <a href="/history" class="btn btn-sm btn-outline-secondary small">
        View All →
      </a>
    </div>

    <?php if (empty($recentTransactions)): ?>
      <div class="text-center py-4 text-muted">
        <div style="font-size:2.5rem;">📭</div>
        <p class="mt-2 mb-1 fw-semibold">No transactions yet</p>
        <p class="small mb-3">Make your first transfer to get started.</p>
        <a href="/transfer" class="btn btn-primary btn-sm px-4">Send Money</a>
      </div>

    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th class="tx-header">Type</th>
              <th class="tx-header">With</th>
              <th class="tx-header">Date</th>
              <th class="tx-header">Comment</th>
              <th class="tx-header text-end">Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $me = (int)Auth::userId();
              foreach ($recentTransactions as $tx):
                $isSent  = (int)$tx['sender_id'] === $me;
                $other   = $isSent
                  ? Security::e((string)($tx['receiver_username'] ?? '#'.(int)$tx['receiver_id']))
                  : Security::e((string)($tx['sender_username']   ?? '#'.(int)$tx['sender_id']));
                $comment = trim((string)($tx['comment'] ?? ''));
            ?>
            <tr>
              <td class="tx-cell">
                <?php if ($isSent): ?>
                  <span class="badge rounded-pill badge-sent">↑ Sent</span>
                <?php else: ?>
                  <span class="badge rounded-pill badge-received">↓ Received</span>
                <?php endif; ?>
              </td>

              <td class="tx-cell">
                <span class="tx-user-text"><?= $other ?></span>
              </td>

              <td class="tx-cell">
                <span class="tx-date"><?= Security::e((string)$tx['created_at']) ?></span>
              </td>

              <td class="tx-cell" style="max-width:160px;">
                <?php if ($comment !== ''): ?>
                  <span class="text-muted fst-italic tx-comment"
                        title="<?= htmlspecialchars($comment, ENT_QUOTES, 'UTF-8') ?>">
                    "<?= Security::e(mb_strimwidth($comment, 0, 35, '…')) ?>"
                  </span>
                <?php else: ?>
                  <span class="text-muted small">—</span>
                <?php endif; ?>
              </td>

              <td class="text-end tx-cell">
                <span class="<?= $isSent ? 'amount-sent' : 'amount-received' ?>">
                  <?= $isSent ? '−' : '+' ?>₹<?= number_format((int)$tx['amount']) ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php else: ?>
<!-- ════════════ LOGGED OUT — HERO ════════════ -->
<div class="row justify-content-center">
  <div class="col-lg-5 col-md-7">
    <div class="card border-0 shadow-sm text-center hero-card">
      <div class="mb-3 hero-icon">🔐</div>
      <h3 class="fw-bold mb-2">Welcome to SecureApp</h3>
      <p class="text-muted mb-4">
        Secure profiles, money transfers, and activity logging — all in one place.
      </p>
      <div class="d-flex justify-content-center gap-3">
        <a class="btn btn-primary px-4" href="/login">Login</a>
        <a class="btn btn-outline-primary px-4" href="/register">Register</a>
      </div>
      <div class="mt-4 pt-3 border-top d-flex justify-content-center gap-4 text-muted flex-wrap hero-badges">
        <span>🔒 Secure Sessions</span>
        <span>💸 Safe Transfers</span>
        <span>📊 Activity Logs</span>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
