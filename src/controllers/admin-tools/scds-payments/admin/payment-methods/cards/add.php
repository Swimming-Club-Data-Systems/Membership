<?php

$pagetitle = "Payment Cards - Payments - SCDS";

$db = app()->db;
$tenant = app()->adminCurrentTenant;
$user = app()->adminCurrentUser;

\Stripe\Stripe::setApiKey(getenv('STRIPE'));

// UPDATING
$customer = $tenant->getStripeCustomer();

$setupIntent = null;
if (!isset($_SESSION['StripeSetupIntentId'])) {
  $setupIntent = \Stripe\SetupIntent::create(
    [
      'customer' => $customer->id,
      'payment_method_types' => ['card']
    ]
  );
  $_SESSION['StripeSetupIntentId'] = $setupIntent->id;
} else {
  $setupIntent = \Stripe\SetupIntent::retrieve(
    $_SESSION['StripeSetupIntentId']
  );
}

if ($setupIntent->status == 'succeeded') {
  unset($_SESSION['StripeSetupIntentId']);
  http_response_code(302);
  header('location: ' . autoUrl('payments-admin/payment-cards'));
  return;
}

$countries = getISOAlpha2Countries();

$fontCss = 'https://fonts.googleapis.com/css?family=Open+Sans';

include BASE_PATH . "views/root/head.php";

?>

<div class="container">

  <?php include BASE_PATH . 'controllers/admin-tools/scds-payments/admin/nav.php'; ?>

  <div class="bg-primary text-white p-4 my-4 d-inline-block rounded">
    <h1 class="">Payment Cards</h1>
    <p class="mb-0">Add and edit your payment cards</p>
  </div>

  <div class="row">
    <div class="col-lg-8">


      <div id="stripe-data" data-stripe-publishable="<?= htmlspecialchars(getenv('STRIPE_PUBLISHABLE')) ?>" data-stripe-font-css="<?= htmlspecialchars($fontCss) ?>">
      </div>

      <h1>Add a payment card</h1>

      <?php if (isset($_SESSION['StripeSetupIntentError'])) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>An error occurred</strong>
          </p>
          <p class="mb-0"><?= htmlspecialchars($_SESSION['StripeSetupIntentError']) ?></p>
        </div>
        <?php unset($_SESSION['StripeSetupIntentError']);  ?>
      <?php } ?>

      <form action="<?= htmlspecialchars(autoUrl("payments-admin/payment-cards/add")) ?>" method="post" id="payment-form" class="mb-5 needs-validation" novalidate>
        <div id="form-hideable" class="show fade">
          <div class="mb-3">
            <label class="form-label" for="cardholder-name">Cardholder name</label>
            <input type="text" class="form-control pm-can-disable" id="cardholder-name" placeholder="C F Frost" required aria-describedby="cardholder-name-help" autocomplete="cc-name">
            <small id="cardholder-name-help" class="form-text text-muted">The name shown on your card</small>
            <div class="invalid-feedback">
              You must provide your full name
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="addr-line-1">Address line 1</label>
            <input type="text" class="form-control pm-can-disable" id="addr-line-1" placeholder="1 Burns Green" required autocomplete="address-line1">
            <div class="invalid-feedback">
              You must provide your address
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="addr-post-code">Post Code</label>
            <input type="text" class="form-control pm-can-disable text-uppercase" id="addr-post-code" placeholder="NE99 1AA" required autocomplete="postal-code">
            <div class="invalid-feedback">
              You must provide your post code
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="addr-post-code">Country</label>
            <select class="form-select pm-can-disable" required id="addr-country" autocomplete="country">
              <?php foreach ($countries as $code => $name) { ?>
                <option <?php if ($code == 'GB') { ?>selected<?php } ?> value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($name) ?></option>
              <?php } ?>
            </select>
            <div class="invalid-feedback">
              You must provide your country
            </div>
          </div>

          <!-- Multiple Part Element -->
          <div class="mb-3">
            <label class="form-label" for="card-number-element">
              Card number
            </label>
            <div class="input-group">
                <span class="input-group-text" id="card-brand-element"><img class="fa fa-fw" src="<?= autoUrl("public/img/stripe/network-svgs/credit-card.svg") ?>" aria-hidden="true"></span>
              <div id="card-number-element" class="form-control stripe-form-control"></div>
              <div id="card-number-element-errors" class="stripe-feedback"></div>
            </div>
          </div>

          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label class="form-label" for="card-expiry-element">
                  Expires
                </label>
                <span id="card-expiry-element" class="form-control pm-can-disable"></span>
                <div id="card-expiry-element-errors" class="stripe-feedback"></div>
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label class="form-label" for="card-cvc-element">
                  CVC
                </label>
                <span id="card-cvc-element" class="form-control pm-can-disable"></span>
                <div id="card-cvc-element-errors" class="stripe-feedback"></div>
              </div>
            </div>
          </div>

        </div>

        <!-- Used to display form errors. -->
        <div id="card-errors" role="alert"></div>

        <p>
          I authorise Swimming Club Data Systems to send instructions to the financial institution that issued my card to take payments from my card account in accordance with the terms of my agreement with you.
        </p>

        <p>
          <button id="card-button" class="btn btn-success" data-secret="<?= htmlspecialchars($setupIntent->client_secret) ?>">Add payment card</button>
        </p>
      </form>
    </div>


  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();

$footer->addJs("public/js/payment-helpers.js");
$footer->addJs("public/js/add-payment-card.js");
$footer->addJs("public/js/NeedsValidation.js");

$footer->render();

?>