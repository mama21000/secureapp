<?php 
use App\Core\Security; 
use App\Core\CSRF; 

// Retrieve the nonce for CSP
$nonce = $_SESSION['csp_nonce'] ?? '';
?>

<style nonce="<?= $nonce ?>">
  .transfer-card {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
  }
  .tx-success-box {
    border-left: 4px solid #16a34a;
    background-color: #f0fdf4;
    color: #166534;
  }
  .tx-id-badge {
    background-color: #16a34a;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.85em;
  }
</style>

<div class="row justify-content-center">
  <div class="col-md-8 col-lg-7">
    <div class="card shadow-sm transfer-card">
      <div class="card-body p-4">
        
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
          <h5 class="mb-0 fw-bold">Money Transfer</h5>
          <a class="btn btn-sm btn-outline-secondary" href="/history">View History</a>
        </div>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= Security::e((string)$error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
          <div class="alert alert-success tx-success-box">
            <div><?= Security::e((string)$success) ?></div>
            <?php if (!empty($tx_id)): ?>
              <div class="small mt-2">
                Transaction ID: <span class="tx-id-badge"><?= (int)$tx_id ?></span>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <?php
        $_SESSION['transfer_token']=bin2hex(random_bytes(32));
        ?>
        <form method="post" action="/transfer" class="row g-3" autocomplete="off">
          <input type="hidden" name="_csrf" value="<?= Security::e(CSRF::token()) ?>">
          <input type="hidden" name="transfer_token" value="<?= Security::e($_SESSION['transfer_token']) ?>">          

          <div class="col-md-6">
            <div class="form-floating">
              <input class="form-control" id="receiver_id" type="number" name="receiver_id" min="1"
       					placeholder="Receiver User ID" value="<?= isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : '' ?>" required>
              <label for="receiver_id">Receiver User ID</label>
            </div>
            <div class="form-text small">Find user IDs using the Search page.</div>
          </div>

          <div class="col-md-6">
            <div class="form-floating">
              <input class="form-control" id="amount" type="number" name="amount" min="1" max="1000000"
                     placeholder="Amount" required>
              <label for="amount">Amount (Rs.)</label>
            </div>
            <div class="form-text small">Min 1, Max 1,000,000.</div>
          </div>

          <div class="col-12">
            <div class="form-floating">
              <input class="form-control" id="comment" name="comment" maxlength="255"
                     placeholder="Comment (optional)">
              <label for="comment">Comment (optional)</label>
            </div>
          </div>

          <div class="col-12 d-flex gap-2 flex-wrap pt-2">
            <button class="btn btn-primary px-4">Send Money</button>
            <a class="btn btn-outline-primary" href="/users/search">Search Users</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
