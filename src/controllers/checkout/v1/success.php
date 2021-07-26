<?php

$db = app()->db;
$tenant = app()->tenant;

$checkoutSession = \SCDS\Checkout\Session::retrieve($id);

if ($checkoutSession->user && $checkoutSession->user != app()->user->getId()) {
  halt(404);
}

$items = $checkoutSession->getItems();

$paymentIntent = $checkoutSession->getPaymentIntent();

$pagetitle = 'Payment successful';

$paymentRequestItems = [];
$paymentRequestItems[] = [
  'label' => 'Subtotal',
  'amount' => $paymentIntent->amount
];

$countries = getISOAlpha2Countries();

$numFormatter = new NumberFormatter("en", NumberFormatter::SPELLOUT);

$markdown = new \ParsedownExtra();
$markdown->setSafeMode(true);

$returnString = 'Return to home page';
$returnUrl = autoUrl('');

if (isset($checkoutSession->metadata->return)) {
  $returnString = $checkoutSession->metadata->return->buttonString;
  $returnUrl = $checkoutSession->metadata->return->url;
  // $checkoutSession->metadata->return->instance?
}

include BASE_PATH . 'views/head.php';

?>

<div class="bg-light py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb" class="d-none">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('payments')) ?>">Payments</a></li>
        <li class="breadcrumb-item active" aria-current="page">Checkout</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>Payment successful</h1>
        <p class="lead mb-0">
          Thank you for making a payment.
        </p>
      </div>
      <div class="col text-lg-end d-none">
        <div class="d-lg-none mt-3"></div>
        <div class="accepted-network-logos">
          <p class="mb-0">
            <img class="apple-pay-row" src="<?= autoUrl("img/stripe/apple-pay-mark.svg", false) ?>" aria-hidden="true"><img class="google-pay-row" src="<?= autoUrl("img/stripe/google-pay-mark.svg", false) ?>" aria-hidden="true"><img class="visa-row" src="<?= autoUrl("img/stripe/visa.svg", false) ?>" aria-hidden="true"><img class="mastercard-row" src="<?= autoUrl("img/stripe/mastercard.svg", false) ?>" aria-hidden="true"><img class="amex-row" src="<?= autoUrl("img/stripe/amex.svg", false) ?>" aria-hidden="true"><img class="amex-row" src="<?= autoUrl("img/stripe/discover.svg", false) ?>" aria-hidden="true"><img class="amex-row" src="<?= autoUrl("img/stripe/diners.svg", false) ?>" aria-hidden="true">
          </p>
        </div>
      </div>
      <div class="col">
        <img src="<?= htmlspecialchars(autoUrl('img/corporate/scds.png')) ?>" class="img-fluid ms-auto d-none d-lg-flex rounded" alt="SCDS Logo" width="75" height="75">
      </div>
    </div>
  </div>
</div>

<div class="container-xl have-full-height">
  <div class="row">
    <main class="col-lg-8">

      <p>
        We're sending a payment receipt to you as confirmation of your payment.
      </p>

      <?php if ($paymentIntent->charges->data[0]->calculated_statement_descriptor) { ?>
        <p>
          This transaction will appear on your statement as <?= htmlspecialchars($paymentIntent->charges->data[0]->calculated_statement_descriptor) ?>, <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($paymentIntent->charges->data[0]->amount), $paymentIntent->charges->data[0]->currency)) ?>.
        </p>
      <?php } ?>

      <div class="d-grid mb-3">
        <a href="<?= htmlspecialchars($returnUrl) ?>" class="btn btn-success">
          <?= htmlspecialchars($returnString) ?>
        </a>
      </div>

      <h2>Items</h2>
      <p>You've paid for the following items</p>

      <ul class="list-group mb-3 accordion" id="entry-list-group">
        <?php foreach ($items as $item) { ?>
          <li class="list-group-item">
            <div class="row">
              <div class="col-8 col-sm-5 col-md-4 col-lg-6">
                <h3><?= htmlspecialchars($item->name) ?></h3>

                <?php if ($item->description) { ?>
                  <?= $markdown->text($item->description) ?>
                <?php } ?>

                <p class="mb-0">
                  <a data-bs-toggle="collapse" href="#<?= htmlspecialchars('item-' . $item->id) ?>" role="button" aria-expanded="false" aria-controls="<?= htmlspecialchars('item-' . $item->id) ?>">
                    View sub-items <i class="fa fa-caret-down" aria-hidden="true"></i>
                  </a>
                </p>

              </div>
              <div class="col text-end">
                <?php if (sizeof($item->subItems) > 0) { ?>
                  <p>
                    <?= mb_convert_case($numFormatter->format(sizeof($item->subItems)), MB_CASE_TITLE_SIMPLE) ?> sub-item<?php if (sizeof($item->subItems) != 1) { ?>s<?php } ?>
                  </p>
                <?php } ?>

                <!--<?php if ($notReady) { ?>
              <p>
                Once you pay for this entry, you won't be able to edit it.
              </p>
              <?php } ?>-->

                <p class="mb-0">
                  <strong>Fee <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($item->amount), $item->currency)) ?></strong>
                </p>
              </div>
            </div>

            <div class="collapse" id="<?= htmlspecialchars('item-' . $item->id) ?>" data-parent="#entry-list-group">
              <div class="mt-3"></div>
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
            </div>
          </li>
        <?php } ?>
        <li class="list-group-item">
          <div class="row align-items-center">
            <div class="col-6">
              <p class="mb-0">
                <strong>Total paid</strong>
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

      <h2>Payment details</h2>

      <dl class="row">

        <?php if ($paymentIntent->charges->data[0]->payment_method_details->card) { ?>
          <dt class="col-3">Card</dt>
          <dd class="col-9">
            <?= htmlspecialchars(getCardBrand($paymentIntent->charges->data[0]->payment_method_details->card->brand) . ' ' . $paymentIntent->charges->data[0]->payment_method_details->card->funding . ' card') ?> &middot;&middot;&middot;&middot; <?= htmlspecialchars($paymentIntent->charges->data[0]->payment_method_details->card->last4) ?>
          </dd>

          <?php if ($paymentIntent->charges->data[0]->payment_method_details->card->wallet) { ?>
            <dt class="col-3">Mobile wallet</dt>
            <dd class="col-9">
              <?= htmlspecialchars(getWalletName($paymentIntent->charges->data[0]->payment_method_details->card->wallet->type)) ?>
            </dd>

            <?php if ($paymentIntent->charges->data[0]->payment_method_details->card->wallet->dynamic_last4) { ?>
              <dt class="col-3">Device account number</dt>
              <dd class="col-9">
                &middot;&middot;&middot;&middot; <?= htmlspecialchars($paymentIntent->charges->data[0]->payment_method_details->card->wallet->dynamic_last4) ?>
              </dd>
            <?php } ?>
          <?php } ?>
        <?php } ?>

        <dt class="col-3">SCDS Checkout reference</dt>
        <dd class="col-9">
          <?= htmlspecialchars($id) ?>
        </dd>

      </dl>

      <h2>Billing details</h2>

      <dl class="row">

        <?php if ($paymentIntent->charges->data[0]->billing_details->address->line1) { ?>
          <dt class="col-3">Name</dt>
          <dd class="col-9">
            <?= htmlspecialchars($paymentIntent->charges->data[0]->billing_details->name) ?>
          </dd>
        <?php } ?>

        <dt class="col-3">Address</dt>
        <dd class="col-9">
          <address class="mb-0">
            <?php if ($paymentIntent->charges->data[0]->billing_details->address->line1) { ?>
              <?= htmlspecialchars($paymentIntent->charges->data[0]->billing_details->address->line1) ?><br>
            <?php } ?>
            <?php if ($paymentIntent->charges->data[0]->billing_details->address->line2) { ?>
              <?= htmlspecialchars($paymentIntent->charges->data[0]->billing_details->address->line2) ?><br>
            <?php } ?>
            <?php if ($paymentIntent->charges->data[0]->billing_details->address->city) { ?>
              <?= htmlspecialchars($paymentIntent->charges->data[0]->billing_details->address->city) ?><br>
            <?php } ?>
            <?php if ($paymentIntent->charges->data[0]->billing_details->address->state) { ?>
              <?= htmlspecialchars($paymentIntent->charges->data[0]->billing_details->address->state) ?><br>
            <?php } ?>
            <?php if ($paymentIntent->charges->data[0]->billing_details->address->country) { ?>
              <?= htmlspecialchars($paymentIntent->charges->data[0]->billing_details->address->country) ?><br>
            <?php } ?>
            <?php if ($paymentIntent->charges->data[0]->billing_details->address->postal_code) { ?>
              <?= htmlspecialchars($paymentIntent->charges->data[0]->billing_details->address->postal_code) ?>
            <?php } ?>
          </address>
        </dd>

      </dl>

      <h2>General notes for payments</h2>

      <p>
        You can find more information about our payment terms and returns policy on our website. All payments are subject to scheme or network rules.
      </p>

      <p>
        Payment services are provided to <?= htmlspecialchars($tenant->getName()) ?> by SCDS and their payment processing partners. PCI DSS compliance is primarily handled by our payment processors.
      </p>

      <p>
        <?= htmlspecialchars($tenant->getName()) ?> may sometimes place a temporary hold of 0GBP to 1GBP or 1USD on your card when you first add it to your account. This is part of the card authorisation process that allows us to determine that your card is valid. This charge will drop off your statement within a few days.
      </p>

      <?php if (isset($paymentIntent->charges->data[0]->payment_method_details->card->brand) && $paymentIntent->charges->data[0]->calculated_statement_descriptor && $paymentIntent->charges->data[0]->payment_method_details->card->brand == 'amex') { ?>
        <p>
          American Express customers may see <strong>Stripe</strong> in online banking and the Amex app while the payment is pending. This will usually update to <strong><?= htmlspecialchars($paymentIntent->charges->data[0]->calculated_statement_descriptor) ?></strong> within 48 hours or when the payment settles.
        </p>
      <?php } ?>

      <h2>Notes for gala entry payments</h2>
      <p>
        In accordance with card network rules, refunds for gala rejections will only be made to the payment card which was used.
      </p>

      <p>
        Should you wish to withdraw your swimmers you will need to contact the gala coordinator. Depending on the gala and host club, you may not be eligible for a refund unless you can provide evidence to back up the reason for withdrawal, such as a doctors note.
      </p>

      <div class="d-grid mb-3">
        <a href="<?= htmlspecialchars($returnUrl) ?>" class="btn btn-success">
          <?= htmlspecialchars($returnString) ?>
        </a>
      </div>

    </main>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
