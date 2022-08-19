<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top rounded-bottom">
  <a class="navbar-brand" href="#">SCDS Payments</a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#admin-payments-nav" aria-controls="admin-payments-nav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="admin-payments-nav">
    <ul class="navbar-nav me-auto">
      <li class="nav-item">
        <a class="nav-link" href="<?= htmlspecialchars(autoUrl('payments-admin')) ?>">Home</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?= htmlspecialchars(autoUrl('payments-admin/direct-debit-instruction')) ?>">Direct Debit</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?= htmlspecialchars(autoUrl('payments-admin/payment-cards')) ?>">Cards</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?= htmlspecialchars(autoUrl('payments-admin/subscriptions')) ?>">Subscriptions</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?= htmlspecialchars(autoUrl('payments-admin/exit')) ?>" title="Return to the <?= htmlspecialchars(app()->adminCurrentTenant->getName()) ?> Membership System">Exit</a>
      </li>
    </ul>
    <span class="navbar-text small">
    <?= htmlspecialchars(app()->adminCurrentUser->getName()) ?>, <?= htmlspecialchars(app()->adminCurrentTenant->getName()) ?>
    </span>
  </div>
</nav>