<?php

if (!isset($_SESSION['StripeSetupIntentSuccess'])) {
  halt(404);
}

\Stripe\Stripe::setApiKey(getenv('STRIPE'));

$setupIntent = \Stripe\SetupIntent::retrieve(
  [
    'id' => $_SESSION['StripeSetupIntentId'],
    'expand' => ['payment_method', 'payment_method.billing_details.address', 'mandate'],
  ]
);
$cardDetails = $setupIntent->payment_method->card;

unset($_SESSION['StripeSetupIntentId']);
unset($_SESSION['StripeSetupIntentSuccess']);

$pagetitle = "Card Setup Successful - Payments - SCDS";

$db = app()->db;
$tenant = app()->adminCurrentTenant;
$user = app()->adminCurrentUser;

include BASE_PATH . "views/root/head.php";

?>

<div class="container-xl">

  <?php include BASE_PATH . 'controllers/admin-tools/scds-payments/admin/nav.php'; ?>

  <div class="row mt-4">
    <div class="col-lg-8">

      <h1>
        New card added successfully
      </h1>

      <p class="lead">
        You'll now be able to use your <?= htmlspecialchars(getCardBrand($cardDetails->brand)) ?> <?= htmlspecialchars($cardDetails->funding) ?> card &#0149;&#0149;&#0149;&#0149; <?= htmlspecialchars($cardDetails->last4) ?> for payments to Swimming Club Data Systems.
      </p>

      <p>
        When using this card, you may be asked to confirm your details for security reasons.
      </p>

      <p>
        <a href="<?= htmlspecialchars(autoUrl('payments-admin/payment-cards')) ?>" class="btn btn-primary">
          Continue
        </a>
      </p>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();

?>