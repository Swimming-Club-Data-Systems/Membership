<?php

use Ramsey\Uuid\Uuid;

$db = app()->db;
$tenant = app()->tenant;

$checkoutSession = \SCDS\Checkout\Session::retrieve($id);

// if ($checkoutSession->user && ($checkoutSession->user != app()->user->getId() || isset($_SESSION['OnboardingSessionId']))) {
//   halt(404);
// }

$items = $checkoutSession->getItems();

$expMonth = date("m");
$expYear = date("Y");

$customer = $db->prepare("SELECT CustomerID FROM stripeCustomers WHERE User = ?");
$customer->execute([$checkoutSession->user]);
$customerId = $customer->fetchColumn();

$numberOfCards = $db->prepare("SELECT COUNT(*) `count`, stripePayMethods.ID FROM stripePayMethods INNER JOIN stripeCustomers ON stripeCustomers.CustomerID = stripePayMethods.Customer WHERE User = ? AND Reusable = ? AND (ExpYear > ? OR (ExpYear = ? AND ExpMonth >= ?))");
$numberOfCards->execute([$checkoutSession->user, 1, $expYear, $expYear, $expMonth]);
$countCards = $numberOfCards->fetch(PDO::FETCH_ASSOC);

$getCards = $db->prepare("SELECT stripePayMethods.ID, `MethodID`, stripePayMethods.Customer, stripePayMethods.Last4, stripePayMethods.Brand FROM stripePayMethods INNER JOIN stripeCustomers ON stripeCustomers.CustomerID = stripePayMethods.Customer WHERE User = ? AND Reusable = ? AND (ExpYear > ? OR (ExpYear = ? AND ExpMonth >= ?)) ORDER BY `Name` ASC");
$getCards->execute([$checkoutSession->user, 1, $expYear, $expYear, $expMonth]);
$cards = $getCards->fetchAll(PDO::FETCH_ASSOC);

$methodId = $customerID = null;

$paymentIntent = $checkoutSession->getPaymentIntent();

$pagetitle = 'Checkout';

$paymentRequestItems = [];
$paymentRequestItems[] = [
  'label' => 'Subtotal',
  'amount' => $paymentIntent->amount
];

$countries = getISOAlpha2Countries();

$numFormatter = new NumberFormatter("en", NumberFormatter::SPELLOUT);

$markdown = new \ParsedownExtra();
$markdown->setSafeMode(true);

$redirect = $checkoutSession->getUrl();
if (isset($checkoutSession->metadata->return) && $checkoutSession->metadata->return->instant) {
  $redirect = $checkoutSession->metadata->return->url;
  // $checkoutSession->metadata->return->instant?
}

$cancelUrl = autoUrl('');
if (isset($checkoutSession->metadata->cancel)) {
  $cancelUrl = $checkoutSession->metadata->cancel->url;
}

include BASE_PATH . 'views/head.php';

?>

<div id="stripe-data" data-stripe-publishable="<?= htmlspecialchars(getenv('STRIPE_PUBLISHABLE')) ?>" data-redirect-url-new="<?= htmlspecialchars($checkoutSession->getUrl()) ?>" data-redirect-url="<?= htmlspecialchars($redirect) ?>" data-org-name="<?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>" data-intent-amount="<?= htmlspecialchars($paymentIntent->amount) ?>" data-intent-currency="<?= htmlspecialchars($paymentIntent->currency) ?>" data-payment-request-line-items="<?= htmlspecialchars(json_encode($paymentRequestItems)) ?>" data-stripe-account-id="<?= htmlspecialchars($tenant->getStripeAccount()) ?>">
</div>

<div class="bg-light py-3 mb-3">
  <div class="container">

    <div class="row mb-4 align-items-center">
      <div class="col-auto">
        <div class="h1 mb-0">
          <a href="<?= htmlspecialchars($cancelUrl) ?>" class="text-decoration-none">
            <?php if ($tenant->getKey('LOGO_DIR')) { ?>
              <img src="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75.png')) ?>" srcset="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75@2x.png')) ?> 2x, <?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75@3x.png')) ?> 3x" alt="<?= htmlspecialchars($tenant->getName()) ?>" class="img-fluid" style="height: 75px">
            <?php } else { ?>
              <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>
            <?php } ?>
          </a>
        </div>
      </div>
      <div class="col-auto ms-auto">
        <a href="<?= htmlspecialchars($cancelUrl) ?>" class="btn btn-outline-dark rounded-pill"><span class="d-none d-lg-inline">Cancel </span><span class="fa fa-close"></span></a>
      </div>

    </div>

    <h1 class="mb-0">
      <span class="text-muted small">Pay <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?></span> <br><?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($checkoutSession->amount), $checkoutSession->currency)) ?>
    </h1>

    <p class="mb-0 mt-3 d-block d-lg-none">You'll pay for <?= htmlspecialchars($numFormatter->format(sizeof($items))) ?> item<?php if (sizeof($items) != 1) { ?>s<?php } ?>. <a data-bs-toggle="collapse" href="#entry-list-group" role="button" aria-expanded="false" aria-controls="entry-list-group">Show details <i class="fa fa-caret-down" aria-hidden="true"></i></a></p>
  </div>
</div>

<div class="container">
  <div class="row justify-content-between">
    <div class="col-lg-4 order-lg-2">

      <div class="position-sticky top-3">

        <p class="d-none d-lg-block">You'll pay for the following item<?php if (sizeof($items) != 1) { ?>s<?php } ?></p>

        <ul class="collapse d-lg-flex list-group mb-3 accordion" id="entry-list-group">
          <?php foreach ($items as $item) { ?>
            <li class="list-group-item">
              <h3><?= htmlspecialchars($item->name) ?></h3>

              <?php if (sizeof($item->subItems) > 0) { ?>
                <p>
                  <?= mb_convert_case($numFormatter->format(sizeof($item->subItems)), MB_CASE_TITLE_SIMPLE) ?> sub-item<?php if (sizeof($item->subItems) != 1) { ?>s<?php } ?>
                </p>
              <?php } ?>

              <div class="row align-items-center">
                <?php if (sizeof($item->subItems) > 0 || $item->description) { ?>
                  <div class="col-auto">
                    <p class="mb-0">
                      <a data-bs-toggle="collapse" href="#<?= htmlspecialchars('item-' . $item->id) ?>" role="button" aria-expanded="false" aria-controls="<?= htmlspecialchars('item-' . $item->id) ?>" class="">
                        Show details <i class="fa fa-caret-down" aria-hidden="true"></i>
                      </a>
                    </p>
                  </div>
                <?php } ?>
                <div class="col-auto ms-auto">
                  <p class="mb-0">
                    <strong>Fee <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($item->amount), $item->currency)) ?></strong>
                  </p>
                </div>
              </div>

              <div class="collapse" id="<?= htmlspecialchars('item-' . $item->id) ?>" data-parent="#entry-list-group">
                <div class="mt-3"></div>
                <?php if ($item->description) { ?>
                  <?= $markdown->text($item->description) ?>
                <?php } ?>

                <?php if (sizeof($item->subItems) > 0) { ?>
                  <ul class="list-unstyled">
                    <?php foreach ($item->subItems as $item) { ?>
                      <li>
                        <div class="row">
                          <div class="col-auto">
                            <?= htmlspecialchars($item->name) ?>
                          </div>
                          <div class="col-auto ms-auto">
                            <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($item->amount), $item->currency)) ?>
                          </div>
                        </div>
                      </li>
                    <?php } ?>
                  </ul>
                <?php } ?>
              </div>
            </li>
          <?php } ?>
          <li class="list-group-item">
            <div class="row align-items-center">
              <div class="col-6">
                <p class="mb-0">
                  <strong>Total to pay</strong>
                </p>
              </div>
              <div class="col text-end">
                <p class="mb-0">
                  <strong><?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($checkoutSession->amount), $checkoutSession->currency)) ?></strong>
                </p>
              </div>
            </div>
          </li>
        </ul>

      </div>
    </div>
    <div class="col-lg-7 order-lg-1">

      <div id="payment-request-card">
        <div class="mb-3">
          <form>
            <div id="payment-request-button">
              <!-- A Stripe Element will be inserted here. -->
            </div>
            <div id="alert-placeholder" class="mt-3"></div>
          </form>
        </div>

        <p class="text-center">Or</p>
      </div>

      <?php if (sizeof($cards) > 0) { ?>
        <div class="card mb-3" id="saved-cards">
          <form id="saved-card-form">
            <div class="card-header" id="device-title">
              Pay with a saved card
            </div>
            <div class="card-body pb-0">

              <div class="mb-3">
                <label class="form-label" for="method">Choose a saved card</label>
                <select class="form-select pm-can-disable" name="method" id="method">
                  <option value="select">Select card</option>
                  <?php foreach ($cards as $card) { ?>
                    <option value="<?= $card['MethodID'] ?>">
                      <?= htmlspecialchars(getCardBrand($card['Brand'])) ?> &#0149;&#0149;&#0149;&#0149; <?= htmlspecialchars($card['Last4']) ?>
                    </option>
                  <?php } ?>
                </select>
              </div>

              <div id="save-card-box" class="d-none">
                <!-- Used to display form errors. -->
                <div id="saved-card-errors" role="alert"></div>

                <p>
                <div class="d-grid">
                  <button id="saved-card-button" class="btn btn-success pm-can-disable" type="button" data-secret="<?= $paymentIntent->client_secret ?>">
                    Pay <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($checkoutSession->amount), $checkoutSession->currency)) ?> now
                  </button>
                </div>
                </p>
              </div>

            </div>
          </form>
        </div>

        <p class="text-center">Or</p>
      <?php } ?>

      <div class="card mb-3">
        <div class="card-header">
          Pay with a new card
        </div>
        <div class="card-body">
          <form id="new-card-form" class="needs-validation" novalidate>
            <div class="mb-3">
              <label class="form-label" for="new-cardholder-name">Cardholder name</label>
              <input type="text" class="form-control pm-can-disable" id="new-cardholder-name" placeholder="C F Frost" required autocomplete="cc-name" aria-describedby="new-cardholder-name-help">
              <small id="new-cardholder-name-help" class="form-text text-muted">The name shown on your card</small>
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
              <select class="form-select pm-can-disable pm-can-disable" required id="addr-country" autocomplete="country">
                <?php foreach ($countries as $code => $name) { ?>
                  <option <?php if ($code == 'GB') { ?>selected<?php } ?> value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($name) ?></option>
                <?php } ?>
              </select>
              <div class="invalid-feedback">
                You must provide your country
              </div>
            </div>

            <!-- Used to display form errors. -->
            <div id="card-errors" role="alert"></div>

            <!-- Multiple Part Element -->
            <div class="mb-3">
              <label class="form-label" for="card-number-element">
                Card number
              </label>
              <div class="input-group has-validation">
                <span class="input-group-text" id="card-brand-element"><img class="fa fa-fw" src="<?= autoUrl("img/stripe/network-svgs/credit-card.svg", false) ?>" aria-hidden="true"></span>
                <div id="card-number-element" class="form-control stripe-form-control pm-can-disable"></div>
                <div id="card-number-element-errors" class="stripe-feedback"></div>
              </div>
            </div>

            <div class="row gx-3">
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

            <!--
              <div class="mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="reuse-card" name="reuse-card" checked>
                  <label class="form-check-label" for="reuse-card">Save this card for future payments</label>
                </div>
              </div>
              -->

            <p>Your card details will be saved for use with future payments</p>

            <!-- Used to display form errors. -->
            <div id="new-card-errors" role="alert"></div>

            <div class="d-grid">
              <button id="new-card-button" class="btn btn-success pm-can-disable" type="submit" data-secret="<?= $paymentIntent->client_secret ?>">
                Pay <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($checkoutSession->amount), $checkoutSession->currency)) ?> now
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>

  <div class="row">
    <div class="col text-center accepted-network-logos py-2">

      <p class="mb-2">We proudly accept</p>

      <?= \SCDS\Checkout\Assets::networkLogos() ?>

    </div>
  </div>

  <div class="row align-items-center my-4 d-none">
    <div class="col-12">
      <img src="<?= htmlspecialchars(autoUrl('img/corporate/scds.png')) ?>" class="img-fluid mb-2 d-none d-lg-flex rounded" alt="SCDS Logo" width="75" height="75">
    </div>
    <div class="col">
      <p class="mb-0 fs-5 lh-1">
        A service provided by Swimming Club Data Systems to <?= htmlspecialchars($tenant->getName()) ?>
      </p>
    </div>
  </div>
</div>


<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/payment-helpers.js");
$footer->addJS("js/checkout/v1/checkout.js");
$footer->addJS("js/NeedsValidation.js");
$footer->render();
