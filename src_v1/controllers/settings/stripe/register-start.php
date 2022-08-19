<?php

if (!isset($_GET['tenant']) || !isset($_GET['tenant'])) halt(404);

$db = app()->db;
$tenant = Tenant::fromUUID($_GET['tenant']);

if ($at = $tenant->getStripeAccount()) {
  // Already got it, halt
  halt(404);
}

// Get User
$getUser = $db->prepare("SELECT Forename, Surname FROM users WHERE UserID = ? AND Tenant = ?");
$getUser->execute([
  $_GET['user'],
  $tenant->getId()
]);
$user = $getUser->fetch(PDO::FETCH_ASSOC);

if (!$user) halt(404);

$pagetitle = "Confirm your password to continue";

include BASE_PATH . "views/root/head.php";

?>

<div class="container-xl">
  <div class="row justify-content-center py-3">
    <div class="col-lg-8 col-md-10">
      <img src="<?= htmlspecialchars(autoUrl("img/corporate/scds.png")) ?>" class="img-fluid d-block mb-5" style="height: 75px;">

      <div class="mb-4 d-inline-block">
        <h1 class="">Stripe Setup</h1>
        <p class="mb-0">Confirm your <?= htmlspecialchars($tenant->getName()) ?> account password to proceed.</p>
      </div>

      <?php if (isset($_SESSION['STRIPE_INVALID_PASSWORD']) && $_SESSION['STRIPE_INVALID_PASSWORD']) { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>Incorrect password</strong>
          </p>
          <p class="mb-0">
            Please try again.
          </p>
        </div>
      <?php unset($_SESSION['STRIPE_INVALID_PASSWORD']); } ?>

      <form method="post" class="needs-validation" novalidate>
        <div class="mb-3">
          <label for="password" class="form-label">
            Password
          </label>
          <input type="password" name="password" id="password" required class="form-control">
          <div class="invalid-feedback">
            Enter your password
          </div>
        </div>

        <?= \SCDS\CSRF::write() ?>

        <p>
          <button type="submit" class="btn btn-primary">
            Confirm
          </button>
        </p>
      </form>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->addJs('js/NeedsValidation.js');
$footer->render();
