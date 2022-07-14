<?php

$pagetitle = "Set up a Direct Debit Instruction - Payments - SCDS";

$db = app()->db;
$tenant = app()->adminCurrentTenant;
$user = app()->adminCurrentUser;

\Stripe\Stripe::setApiKey(getenv('STRIPE'));

// UPDATING
$customer = $tenant->getStripeCustomer();

$session = null;

$successUrl = autoUrl('payments-admin/direct-debit-instruction/set-up/success?session_id={CHECKOUT_SESSION_ID}');
$cancelUrl = autoUrl('payments-admin/direct-debit-instruction/set-up');

$session = \Stripe\Checkout\Session::create([
  'payment_method_types' => ['bacs_debit'],
  'mode' => 'setup',
  'customer' => $customer->id,
  'success_url' => $successUrl,
  'cancel_url' => $cancelUrl,
  'locale' => 'en-GB',
  'metadata' => [
    'session_type' => 'direct_debit_setup',
  ],
]);

include BASE_PATH . "views/root/head.php";

?>

<div class="container-xl">

<div id="stripe-data" data-stripe-publishable="<?= htmlspecialchars(getenv('STRIPE_PUBLISHABLE')) ?>" data-session-id="<?= htmlspecialchars($session->id) ?>">
</div>

  <?php include BASE_PATH . 'controllers/admin-tools/scds-payments/admin/nav.php'; ?>

  <div class="row align-items-center">
    <div class="col">
      <div class="bg-primary text-white p-4 my-4 d-inline-block rounded">
        <h1>
          Set up a Direct Debit
        </h1>
        <p class="mb-0">
          Direct debit is the easiest way to pay your monthly subscription
        </p>
      </div>
    </div>
    <div class="d-none d-sm-flex col-sm-auto ms-auto">
      <img style="max-height:50px;" src="<?= htmlspecialchars(autoUrl("img/directdebit/directdebit.png", false)) ?>" srcset="<?= htmlspecialchars(autoUrl("img/directdebit/directdebit@2x.png", false)) ?> 2x, <?= htmlspecialchars(autoUrl("img/directdebit/directdebit@3x.png", false)) ?> 3x" alt="Direct
				Debit Logo">
    </div>
  </div>

  <div class="row pb-3">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['StripeDDError']) && $_SESSION['StripeDDError']) { ?>
        <div class="alert alert-error">
          <p class="mb-0">
            <strong>We've encountered a problem setting up your direct debit</strong>
          </p>
          <p>
            You may not have supplied all of the information required or taken too long to complete the form.
          </p>

          <p class="mb-0">
            Please try again.
          </p>
        </div>
      <?php unset($_SESSION['StripeDDError']);
      } ?>

      <h2>To begin, you will need</h2>
      <ul>
        <li>The name of the bank account holder</li>
        <li>Your sort code and bank account number</li>
        <li>The address of the bank account holder</li>
      </ul>

      <p>You must be authorised to create a direct debit mandate on the account.</p>

      <h2>You will not need</h2>
      <ul>
        <li>The name or address of your bank - we'll fetch this automatically</li>
      </ul>

      <p>
        Direct Debit makes payments simpler for everyone involved. Payments are taken automatically, so there is no need to adjust standing orders and payments are automatically marked as paid by our systems.
      </p>
      <p>
        We'll usually generate a bill and charge you your fees on or soon after the first working day of each month. It can take several days for the money to leave your organisation's bank account.
      </p>

      <p>
        <button id="set-up-button" class="btn btn-primary">
          Set up
        </button>
      </p>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->addJs("js/scds-payments/direct-debit/setup.js");
$footer->render();
