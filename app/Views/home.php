<?php use App\Core\Security; use App\Core\Auth; ?>

<?php if (!empty($loggedIn)): ?>
<!-- ════════════ LOGGED IN — DASHBOARD ════════════ -->

<!-- Welcome Row -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <div>
    <h4 class="fw-bold mb-1">
      Welcome back, <?= Security::e((string)$username) ?> 👋
    </h4>
    <p class="text-muted mb-0" style="font-size:.9rem;">
      Here's a summary of your account.
    </p>
  </div>
  <a href="/transfer" class="btn btn-primary px-4">+ New Transfer</a>
</div>

<!-- Row 1: Balance + Quick Links -->
<div class="row g-3 mb-4">

  <!-- Balance Card -->
  <div class="col-md-4">
    <div class="card h-100 border-0 text-white"
         style="background:linear-gradient(135deg,#1a56db,#6366f1);border-radius:14px;">
      <div class="card-body p-4">
        <p class="mb-1 text-white-50"
           style="font-size:.75rem;text-transform:uppercase;letter-spacing:.8px;font-weight:600;">
          Available Balance
        </p>
        <h2 class="fw-bold mb-1" style="font-size:2rem;letter-spacing:-1px;">
          ₹<?= number_format((int)($balance ?? 0)) ?>
        </h2>
        <p class="mb-0 text-white-50" style="font-size:.82rem;">
          Account: <strong class="text-white"><?= Security::e((string)$username) ?></strong>
        </p>
      </div>
    </div>
  </div>

  <!-- Quick Links Grid -->
  <div class="col-md-8">
    <div class="row g-3 h-100">

      <div class="col-6 col-sm-3">
        <a href="/profile/edit" class="text-decoration-none">
          <div class="card h-100 border text-center py-3 px-2"
               style="border-radius:12px;border-color:#e2e8f0 !important;transition:all .18s;"
               onmouseover="this.style.borderColor='#6366f1';this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(99,102,241,.15)';"
               onmouseout="this.style.borderColor='#e2e8f0';this.style.transform='';this.style.boxShadow='';">
            <div class="mb-2" style="font-size:1.6rem;">👤</div>
            <div class="fw-semibold" style="font-size:.85rem;color:#1e293b;">Profile</div>
          </div>
        </a>
      </div>

      <div class="col-6 col-sm-3">
        <a href="/users/search" class="text-decoration-none">
          <div class="card h-100 border text-center py-3 px-2"
               style="border-radius:12px;border-color:#e2e8f0 !important;transition:all .18s;"
               onmouseover="this.style.borderColor='#6366f1';this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(99,102,241,.15)';"
               onmouseout="this.style.borderColor='#e2e8f0';this.style.transform='';this.style.boxShadow='';">
            <div class="mb-2" style="font-size:1.6rem;">🔍</div>
            <div class="fw-semibold" style="font-size:.85rem;color:#1e293b;">Search</div>
          </div>
        </a>
      </div>

      <div class="col-6 col-sm-3">
        <a href="/transfer" class="text-decoration-none">
          <div class="card h-100 border text-center py-3 px-2"
               style="border-radius:12px;border-color:#e2e8f0 !important;transition:all .18s;"
               onmouseover="this.style.borderColor='#6366f1';this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(99,102,241,.15)';"
               onmouseout="this.style.borderColor='#e2e8f0';this.style.transform='';this.style.boxShadow='';">
            <div class="mb-2" style="font-size:1.6rem;">💸</div>
            <div class="fw-semibold" style="font-size:.85rem;color:#1e293b;">Transfer</div>
          </div>
        </a>
      </div>

      <div class="col-6 col-sm-3">
        <a href="/history" class="text-decoration-none">
          <div class="card h-100 border text-center py-3 px-2"
               style="border-radius:12px;border-color:#e2e8f0 !important;transition:all .18s;"
               onmouseover="this.style.borderColor='#6366f1';this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(99,102,241,.15)';"
               onmouseout="this.style.borderColor='#e2e8f0';this.style.transform='';this.style.boxShadow='';">
            <div class="mb-2" style="font-size:1.6rem;">📋</div>
            <div class="fw-semibold" style="font-size:.85rem;color:#1e293b;">History</div>
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
      <a href="/history" class="btn btn-sm btn-outline-secondary" style="font-size:.8rem;">
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
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;border-bottom:2px solid #e2e8f0;padding:8px 12px;">Type</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;border-bottom:2px solid #e2e8f0;padding:8px 12px;">With</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;border-bottom:2px solid #e2e8f0;padding:8px 12px;">Date</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;border-bottom:2px solid #e2e8f0;padding:8px 12px;">Comment</th>
              <th class="text-end" style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;border-bottom:2px solid #e2e8f0;padding:8px 12px;">Amount</th>
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
              <td style="padding:11px 12px;">
                <?php if ($isSent): ?>
                  <span class="badge rounded-pill"
                        style="background:#fee2e2;color:#dc2626;font-size:.75rem;font-weight:600;padding:5px 10px;">
                    ↑ Sent
                  </span>
                <?php else: ?>
                  <span class="badge rounded-pill"
                        style="background:#dcfce7;color:#16a34a;font-size:.75rem;font-weight:600;padding:5px 10px;">
                    ↓ Received
                  </span>
                <?php endif; ?>
              </td>

              <td style="padding:11px 12px;">
                <span class="fw-semibold" style="font-size:.88rem;color:#1e293b;">
                  <?= $other ?>
                </span>
              </td>

              <td style="padding:11px 12px;">
                <span class="text-muted" style="font-size:.82rem;white-space:nowrap;">
                  <?= Security::e((string)$tx['created_at']) ?>
                </span>
              </td>

              <td style="padding:11px 12px;max-width:160px;">
                <?php if ($comment !== ''): ?>
                  <span class="text-muted fst-italic"
                        style="font-size:.82rem;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                        title="<?= htmlspecialchars($comment, ENT_QUOTES, 'UTF-8') ?>">
                    "<?= Security::e(mb_strimwidth($comment, 0, 35, '…')) ?>"
                  </span>
                <?php else: ?>
                  <span class="text-muted" style="font-size:.8rem;">—</span>
                <?php endif; ?>
              </td>

              <td class="text-end" style="padding:11px 12px;">
                <span class="fw-bold"
                      style="font-size:.95rem;color:<?= $isSent ? '#dc2626' : '#16a34a' ?>;">
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
    <div class="card border-0 shadow-sm text-center p-5" style="border-radius:16px;">
      <div class="mb-3" style="font-size:2.8rem;">🔐</div>
      <h3 class="fw-bold mb-2">Welcome to SecureApp</h3>
      <p class="text-muted mb-4">
        Secure profiles, money transfers, and activity logging — all in one place.
      </p>
      <div class="d-flex justify-content-center gap-3">
        <a class="btn btn-primary px-4" href="/login">Login</a>
        <a class="btn btn-outline-primary px-4" href="/register">Register</a>
      </div>
      <div class="mt-4 pt-3 border-top d-flex justify-content-center gap-4 text-muted flex-wrap"
           style="font-size:.82rem;">
        <span>🔒 Secure Sessions</span>
        <span>💸 Safe Transfers</span>
        <span>📊 Activity Logs</span>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>