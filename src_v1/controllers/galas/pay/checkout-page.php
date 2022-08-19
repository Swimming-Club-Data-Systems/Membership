<?php

$db = app()->db;
$tenant = app()->tenant;

\Stripe\Stripe::setApiKey(getenv('STRIPE'));

try {
  $res = \Stripe\ApplePayDomain::create([
    'domain_name' => app('request')->hostname
  ], [
    'stripe_account' => $tenant->getStripeAccount()
  ]);
  // pre($res);
  // $res = \Stripe\ApplePayDomain::create([
  //   'domain_name' => app('request')->hostname
  // ]);
  // pre($res);
} catch (Exception $e) {
  // Not the end of the world so report the error and continue.
  // Any errors can be resolved later.
  reportError($e);
}

// DELETE
// try {
//   $apd = \Stripe\ApplePayDomain::retrieve(
//     'STRIPE APPLE PAY DOMAIN ID',
//     [
//       'stripe_account' => $tenant->getStripeAccount()
//     ]
//   );
//   $apd->delete();
// } catch (Exception $e) {
//   pre($e);
// }

// try {
//   pre(\Stripe\ApplePayDomain::all([
//     'limit' => 20
//   ], [
//     'stripe_account' => $tenant->getStripeAccount()
//   ]));
//   // pre(\Stripe\ApplePayDomain::all([
//   //   'limit' => 20
//   // ]));
//   pre($tenant->getStripeAccount());
// } catch (Exception $e) {
//   // Not the end of the world so report the error and continue.
//   // Any errors can be resolved later.
//   pre($e);
// }

$expMonth = date("m");
$expYear = date("Y");

$customer = $db->prepare("SELECT CustomerID FROM stripeCustomers WHERE User = ?");
$customer->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
$customerId = $customer->fetchColumn();

$numberOfCards = $db->prepare("SELECT COUNT(*) `count`, stripePayMethods.ID FROM stripePayMethods INNER JOIN stripeCustomers ON stripeCustomers.CustomerID = stripePayMethods.Customer WHERE User = ? AND Reusable = ? AND (ExpYear > ? OR (ExpYear = ? AND ExpMonth >= ?))");
$numberOfCards->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], 1, $expYear, $expYear, $expMonth]);
$countCards = $numberOfCards->fetch(PDO::FETCH_ASSOC);

$getCards = $db->prepare("SELECT stripePayMethods.ID, `MethodID`, stripePayMethods.Customer, stripePayMethods.Last4, stripePayMethods.Brand FROM stripePayMethods INNER JOIN stripeCustomers ON stripeCustomers.CustomerID = stripePayMethods.Customer WHERE User = ? AND Reusable = ? AND (ExpYear > ? OR (ExpYear = ? AND ExpMonth >= ?)) ORDER BY `Name` ASC");
$getCards->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], 1, $expYear, $expYear, $expMonth]);
$cards = $getCards->fetchAll(PDO::FETCH_ASSOC);

$methodId = $customerID = null;

$selected = null;
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentMethodID'])) {
  $selected = $_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentMethodID'];

  foreach ($cards as $card) {
    if ($card['ID'] == $_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentMethodID']) {
      $methodId = $card['MethodID'];
      $customerID = $card['Customer'];
    }
  }
}

$swimsArray = [
  '25Free' => '25&nbsp;Free',
  '50Free' => '50&nbsp;Free',
  '100Free' => '100&nbsp;Free',
  '200Free' => '200&nbsp;Free',
  '400Free' => '400&nbsp;Free',
  '800Free' => '800&nbsp;Free',
  '1500Free' => '1500&nbsp;Free',
  '25Back' => '25&nbsp;Back',
  '50Back' => '50&nbsp;Back',
  '100Back' => '100&nbsp;Back',
  '200Back' => '200&nbsp;Back',
  '25Breast' => '25&nbsp;Breast',
  '50Breast' => '50&nbsp;Breast',
  '100Breast' => '100&nbsp;Breast',
  '200Breast' => '200&nbsp;Breast',
  '25Fly' => '25&nbsp;Fly',
  '50Fly' => '50&nbsp;Fly',
  '100Fly' => '100&nbsp;Fly',
  '200Fly' => '200&nbsp;Fly',
  '100IM' => '100&nbsp;IM',
  '150IM' => '150&nbsp;IM',
  '200IM' => '200&nbsp;IM',
  '400IM' => '400&nbsp;IM'
];

$rowArray = [1, null, null, null, null, null, 2, 1,  null, null, 2, 1, null, null, 2, 1, null, null, 2, 1, null, null, 2];
$rowArrayText = ["Freestyle", null, null, null, null, null, 2, "Backstroke",  null, null, 2, "Breaststroke", null, null, 2, "Butterfly", null, null, 2, "Individual Medley", null, null, 2];

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['PaidEntries'])) {
  halt(404);
}

$intent = null;

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentIntent'])) {
  $intent = \Stripe\PaymentIntent::retrieve(
    $_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentIntent'],
    [
      'stripe_account' => $tenant->getStripeAccount()
    ]
  );
} else {
  header("Location: " . autoUrl("galas/pay-for-entries"));
  return;
}

$getId = $db->prepare("SELECT ID FROM stripePayments WHERE Intent = ?");
$getId->execute([
  $intent->id
]);
$databaseId = $getId->fetchColumn();
$paymentDatabaseId = $databaseId;

if ($intent->status == 'succeeded') {
  header("Location: " . autoUrl("payments/card-transactions"));
  return;
}

if ($methodId != null && $customerID != null) {
  $intent = \Stripe\PaymentIntent::update(
    $_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentIntent'],
    [
      'payment_method' => $methodId,
      'customer' => $customerID,
    ],
    [
      'stripe_account' => $tenant->getStripeAccount()
    ]
  );
} else if ($customerId != null) {
  $intent = \Stripe\PaymentIntent::update(
    $_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentIntent'],
    [
      'customer' => $customerId,
    ],
    [
      'stripe_account' => $tenant->getStripeAccount()
    ]
  );
}

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentMethodID'])) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['AddNewCard'] = true;
}

$getEntriesByPI = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE StripePayment = ?");
$getEntriesByPI->execute([
  $paymentDatabaseId
]);

$countries = getISOAlpha2Countries();

$fontCss = 'https://fonts.googleapis.com/css?family=Open+Sans';
if (!app()->tenant->isCLS()) {
  $fontCss = 'https://fonts.googleapis.com/css?family=Source+Sans+Pro';
}

$entryRequestDetails = [];
$entryRequestDetails[] = [
  'label' => 'Subtotal',
  'amount' => $intent->amount
];

$numFormatter = new NumberFormatter("en", NumberFormatter::SPELLOUT);

$pagetitle = "Checkout";
include BASE_PATH . "views/header.php";
include BASE_PATH . "controllers/galas/galaMenu.php";
?>

<div id="stripe-data" data-stripe-publishable="<?= htmlspecialchars(getenv('STRIPE_PUBLISHABLE')) ?>" data-stripe-font-css="<?= htmlspecialchars($fontCss) ?>" data-redirect-url-new="<?= htmlspecialchars(autoUrl("galas/pay-for-entries/complete/new")) ?>" data-redirect-url="<?= htmlspecialchars(autoUrl("galas/pay-for-entries/complete")) ?>" data-org-name="<?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>" data-intent-amount="<?= htmlspecialchars($intent->amount) ?>" data-intent-currency="<?= htmlspecialchars($intent->currency) ?>" data-payment-request-line-items="<?= htmlspecialchars(json_encode($entryRequestDetails)) ?>" data-stripe-account-id="<?= htmlspecialchars($tenant->getStripeAccount()) ?>">
</div>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas") ?>">Galas</a></li>
        <li class="breadcrumb-item active" aria-current="page">Pay for entries</li>
      </ol>
    </nav>

    <div class="row align-items-center justify-content-between">
      <div class="col-lg-7">
        <h1>Pay for gala entries</h1>
        <p class="lead mb-0">Checkout</p>
      </div>
      <div class="col text-lg-end">
        <div class="d-lg-none mt-3"></div>
        <div class="accepted-network-logos">
          <p class="mb-0">
            <img class="apple-pay-row" src="<?= autoUrl("img/stripe/apple-pay-mark.svg", false) ?>" aria-hidden="true"><img class="google-pay-row" src="<?= autoUrl("img/stripe/google-pay-mark.svg", false) ?>" aria-hidden="true"><img class="visa-row" src="<?= autoUrl("img/stripe/visa.svg", false) ?>" aria-hidden="true"><img class="mastercard-row" src="<?= autoUrl("img/stripe/mastercard.svg", false) ?>" aria-hidden="true"><img class="amex-row" src="<?= autoUrl("img/stripe/amex.svg", false) ?>" aria-hidden="true">
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="row justify-content-between">
    <div class="col-lg-4 order-lg-2">

      <div class="position-sticky top-3">

        <h2>Selected entries</h2>
        <p>You'll pay for the following gala entries</p>

        <ul class="list-group mb-3 accordion" id="entry-list-group">
          <?php while ($entry = $getEntriesByPI->fetch(PDO::FETCH_ASSOC)) {
            $notReady = !$entry['EntryProcessed'];
            $galaData = new GalaPrices($db, $entry['GalaID']);
          ?>
            <li class="list-group-item">
              <div class="row">
                <div class="col-8 col-sm-5 col-md-4 col-lg-6">
                  <h3><?= htmlspecialchars($entry['MForename'] . ' ' . $entry['MSurname']) ?> <br><small><?= htmlspecialchars($entry['GalaName']) ?></small></h3>
                  <p class="mb-0">
                    <a data-bs-toggle="collapse" href="#swims-<?= $entry['EntryID'] ?>" role="button" aria-expanded="false" aria-controls="swims-<?= $entry['EntryID'] ?>">
                      View swims <i class="fa fa-caret-down" aria-hidden="true"></i>
                    </a>
                  </p>
                  <div class="collapse" id="swims-<?= $entry['EntryID'] ?>" data-parent="#entry-list-group">
                    <div class="mt-3"></div>
                    <ul class="list-unstyled">
                      <?php $count = 0; ?>
                      <?php foreach ($swimsArray as $colTitle => $text) { ?>
                        <?php if ($entry[$colTitle]) {
                          $count++; ?>
                          <li class="row">
                            <div class="col">
                              <?= $text ?>
                            </div>
                            <?php if ($galaData->getEvent($colTitle)->isEnabled()) { ?>
                              <div class="col">
                                &pound;<?= $galaData->getEvent($colTitle)->getPriceAsString() ?>
                              </div>
                            <?php } ?>
                          </li>
                        <?php } ?>
                      <?php } ?>
                  </div>
                </div>
                <div class="col text-end">
                  <p>
                    <?= mb_convert_case($numFormatter->format($count), MB_CASE_TITLE_SIMPLE) ?> event<?php if ($count != 1) { ?>s<?php } ?>
                  </p>

                  <!--<?php if ($notReady) { ?>
              <p>
                Once you pay for this entry, you won't be able to edit it.
              </p>
              <?php } ?>-->

                  <p class="mb-0">
                    <strong>Fee &pound;<?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $entry['FeeToPay'])->toScale(2))) ?></strong>
                  </p>
                </div>
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
                  <strong>&pound;<?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $intent->amount))->withPointMovedLeft(2)->toScale(2)) ?></strong>
                </p>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
    <div class="col-lg-7 order-lg-1">
      <h2 class="mb-3">Payment details</h2>

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
                  <button id="saved-card-button" class="btn btn-success pm-can-disable" type="button" data-secret="<?= $intent->client_secret ?>">
                    Pay &pound;<?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $intent->amount))->withPointMovedLeft(2)->toScale(2)) ?> now
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

            <p class="mb-0">
            <div class="d-grid">
              <button id="new-card-button" class="btn btn-success pm-can-disable" type="submit" data-secret="<?= $intent->client_secret ?>">
                Pay &pound;<?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $intent->amount))->withPointMovedLeft(2)->toScale(2)) ?> now
              </button>
            </div>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/payment-helpers.js");
$footer->addJS("js/gala-checkout.js");
$footer->addJS("js/NeedsValidation.js");
$footer->render(); ?>