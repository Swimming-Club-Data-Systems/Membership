<?php

$fluidContainer = true;
$pagetitle = 'Stripe Payment Services Options';

$db = app()->db;
$tenant = app()->tenant;

\Stripe\Stripe::setApiKey(getenv('STRIPE'));
$at = app()->tenant->getStripeAccount();

$stripeAccount = \Stripe\Account::retrieve($at);

$supportsDirectDebit = isset($stripeAccount->capabilities->bacs_debit_payments) && $stripeAccount->capabilities->bacs_debit_payments == 'active';

$countries = getISOAlpha2Countries();

$vars = [
  'GALA_CARD_PAYMENTS_ALLOWED' => true,
  'ALLOW_STRIPE_DIRECT_DEBIT_SET_UP' => false,
  'USE_STRIPE_DIRECT_DEBIT' => false,
  'ALLOW_DIRECT_DEBIT_OPT_OUT' => false,
];
$disabled = [
  'GALA_CARD_PAYMENTS_ALLOWED' => '',
  'ALLOW_STRIPE_DIRECT_DEBIT_SET_UP' => '',
  'USE_STRIPE_DIRECT_DEBIT' => '',
  'ALLOW_DIRECT_DEBIT_OPT_OUT' => '',
];

foreach ($vars as $key => $value) {
  if (($value = $tenant->getKey($key)) != null) {
    $vars[$key] = bool($value);
  }
}

if ($vars['USE_STRIPE_DIRECT_DEBIT'] || !$supportsDirectDebit) {
  $disabled['ALLOW_STRIPE_DIRECT_DEBIT_SET_UP'] = ' disabled ';
  $disabled['USE_STRIPE_DIRECT_DEBIT'] = ' disabled ';
}

if ($vars['USE_STRIPE_DIRECT_DEBIT']) {
  $vars['ALLOW_STRIPE_DIRECT_DEBIT_SET_UP'] = true;
}

$phone = null;
if (isset($stripeAccount->business_profile->support_phone)) {
  $phone = $stripeAccount->business_profile->support_phone;
}
try {
  $number = \Brick\PhoneNumber\PhoneNumber::parse((string) $phone);
  $phone = $number->formatForCallingFrom('GB');
} catch (Exception $e) {
}

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">
  <div class="row justify-content-between">
    <aside class="col-md-3 d-none d-md-block">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/settings/SettingsLinkGroup.json'));
      echo $list->render('settings-stripe');
      ?>
    </aside>
    <div class="col-md-9">
      <main>

        <h1>
          Payment services
        </h1>
        <p class="lead">
          Manage your Stripe connection
        </p>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Stripe-Reg-Success'])) { ?>
          <div class="alert alert-success">
            <p class="mb-0">
              <strong>We've connected your Stripe Account</strong>
            </p>
            <p class="mb-0">
              Find out about Stripe <a href="https://stripe.com/gb" target="_blank">on their website</a>.
            </p>
          </div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['Stripe-Reg-Success']);
        } ?>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Stripe-Reg-Error'])) { ?>
          <div class="alert alert-danger">
            <p class="mb-0">
              <strong>We were unable to connect your Stripe Account</strong>
            </p>
            <p class="mb-0">
              <a href="<?= htmlspecialchars(autoUrl("settings/stripe/register")) ?>" class="alert-link">Try again now</a> or try again later.
            </p>
          </div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['Stripe-Reg-Error']);
        } ?>

        <?php if ($at) { ?>

          <h2>
            Stripe Account
          </h2>

          <p>
            Your Stripe account (<span class="font-monospace"><?= htmlspecialchars($at) ?></span>) is currently connected.
          </p>

          <h3>
            Business details
          </h3>

          <dl class="row">
            <dt class="col-sm-3">Business name</dt>
            <dd class="col-sm-9"><?php if (isset($stripeAccount->business_profile->name)) { ?><?= htmlspecialchars($stripeAccount->business_profile->name) ?><?php } else { ?>Not set<?php } ?></dd>

            <dt class="col-sm-3">Support email</dt>
            <dd class="col-sm-9"><?php if (isset($stripeAccount->business_profile->support_email)) { ?><?= htmlspecialchars($stripeAccount->business_profile->support_email) ?><?php } else { ?>Not set<?php } ?></dd>

            <dt class="col-sm-3">Support phone</dt>
            <dd class="col-sm-9"><?php if ($phone) { ?><?= htmlspecialchars($phone) ?><?php } else { ?>Not set<?php } ?></dd>

            <dt class="col-sm-3">Support url</dt>
            <dd class="col-sm-9 text-truncate"><?php if (isset($stripeAccount->business_profile->support_url)) { ?><a href="<?= htmlspecialchars($stripeAccount->business_profile->support_url) ?>" target="_blank"><?= htmlspecialchars(trim(str_replace(['https://www.', 'http://www.', 'http://', 'https://'], '', $stripeAccount->business_profile->support_url), '/')) ?></a><?php } else { ?>Not set<?php } ?></dd>

            <dt class="col-sm-3">Business url</dt>
            <dd class="col-sm-9 text-truncate"><?php if (isset($stripeAccount->business_profile->url)) { ?><a href="<?= htmlspecialchars($stripeAccount->business_profile->url) ?>" target="_blank"><?= htmlspecialchars(trim(str_replace(['https://www.', 'http://www.', 'http://', 'https://'], '', $stripeAccount->business_profile->url), '/')) ?></a><?php } else { ?>Not set<?php } ?></dd>

            <dt class="col-sm-3">Statement descriptor</dt>
            <dd class="col-sm-9 font-monospace"><?php if (isset($stripeAccount->settings->payments->statement_descriptor)) { ?><?= htmlspecialchars($stripeAccount->settings->payments->statement_descriptor) ?><?php } else { ?>Not set<?php } ?></dd>

            <dt class="col-sm-3">Short statement descriptor</dt>
            <dd class="col-sm-9 font-monospace"><?php if (isset($stripeAccount->settings->card_payments->statement_descriptor_prefix)) { ?><?= htmlspecialchars($stripeAccount->settings->card_payments->statement_descriptor_prefix) ?><?php } else { ?>Not set<?php } ?></dd>

            <dt class="col-sm-3">Administrator email</dt>
            <dd class="col-sm-9 text-truncate"><?php if (isset($stripeAccount->email)) { ?><a href="mailto:<?= htmlspecialchars($stripeAccount->email) ?>"><?= htmlspecialchars($stripeAccount->email) ?></a><?php } else { ?>Not set<?php } ?></dd>

            <dt class="col-sm-3">Details submitted</dt>
            <dd class="col-sm-9 text-truncate"><?php if (isset($stripeAccount->details_submitted) && $stripeAccount->details_submitted) { ?>Yes<?php } else { ?>No<?php } ?></dd>

            <dt class="col-sm-3">Payouts enabled</dt>
            <dd class="col-sm-9 text-truncate"><?php if (isset($stripeAccount->payouts_enabled) && $stripeAccount->payouts_enabled) { ?>Yes<?php } else { ?>No<?php } ?></dd>

            <dt class="col-sm-3">Country</dt>
            <dd class="col-sm-9 text-truncate"><?php if (isset($stripeAccount->country)) { ?><?= htmlspecialchars($countries[$stripeAccount->country]) ?><?php } else { ?>Not set<?php } ?></dd>

            <dt class="col-sm-3">Default currency</dt>
            <dd class="col-sm-9 text-truncate"><?php if (isset($stripeAccount->default_currency)) { ?><?= htmlspecialchars(mb_strtoupper($stripeAccount->default_currency)) ?> (SCDS Membership will always charge in GBP)<?php } else { ?>Not set<?php } ?></dd>
          </dl>

          <p>
            You can change the above details in your Stripe account dashboard.
          </p>

          <p>
            <a href="https://dashboard.stripe.com" class="btn btn-primary">
              Stripe Dashboard
            </a>
          </p>

          <h2>
            Options
          </h2>

          <form method="post">
            <div class="mb-3">
              <div class="form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="GALA_CARD_PAYMENTS_ALLOWED" name="GALA_CARD_PAYMENTS_ALLOWED" <?php if (bool($vars['GALA_CARD_PAYMENTS_ALLOWED'])) { ?>checked<?php } ?> <?= $disabled['GALA_CARD_PAYMENTS_ALLOWED'] ?>>
                <label class="form-check-label" for="GALA_CARD_PAYMENTS_ALLOWED">Allow card payments for gala entries</label>
              </div>
            </div>

            <?php if (!$supportsDirectDebit) { ?>
              <p>
                BACS Direct Debit has not been enabled on your Stripe account. Please enable it through your Stripe Dashboard to turn on Direct Debit features.
              </p>
            <?php } ?>

            <div class="mb-3">
              <div class="form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="ALLOW_STRIPE_DIRECT_DEBIT_SET_UP" name="ALLOW_STRIPE_DIRECT_DEBIT_SET_UP" <?php if (bool($vars['ALLOW_STRIPE_DIRECT_DEBIT_SET_UP'])) { ?>checked<?php } ?> <?= $disabled['ALLOW_STRIPE_DIRECT_DEBIT_SET_UP'] ?>>
                <label class="form-check-label" for="ALLOW_STRIPE_DIRECT_DEBIT_SET_UP">Allow users to set up a Direct Debit mandate with Stripe</label>
              </div>
            </div>

            <div class="mb-3">
              <div class="form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="USE_STRIPE_DIRECT_DEBIT" name="USE_STRIPE_DIRECT_DEBIT" <?php if (bool($vars['USE_STRIPE_DIRECT_DEBIT'])) { ?>checked<?php } ?> <?= $disabled['USE_STRIPE_DIRECT_DEBIT'] ?> aria-describedby="USE_STRIPE_DIRECT_DEBIT-help">
                <label class="form-check-label" for="USE_STRIPE_DIRECT_DEBIT">Use Stripe for Direct Debit rather than GoCardless</label>
              </div>
              <small id="USE_STRIPE_DIRECT_DEBIT-help">Once you enable Stripe Direct Debit, GoCardless will stop working and this change can not be reversed.</small>
            </div>

            <div class="mb-3">
              <div class="form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="ALLOW_DIRECT_DEBIT_OPT_OUT" name="ALLOW_DIRECT_DEBIT_OPT_OUT" <?php if (bool($vars['ALLOW_DIRECT_DEBIT_OPT_OUT'])) { ?>checked<?php } ?> aria-describedby="ALLOW_DIRECT_DEBIT_OPT_OUT-help">
                <label class="form-check-label" for="ALLOW_DIRECT_DEBIT_OPT_OUT">Allow users to choose not to use Direct Debit</label>
              </div>
              <small id="ALLOW_DIRECT_DEBIT_OPT_OUT-help">If some of your users will continue to pay by standing order, enable this option so that they aren't forced to set up a Direct Debit mandate during registration or renewal.</small>
            </div>

            <p>
              <button type="submit" class="btn btn-success">
                Save
              </button>
            </p>
          </form>

        <?php } else { ?>

          <p>
            The membership system supports credit and debit card payments for gala entries. We hope to bring support for card payments in other areas, such as registration and renewal in due course.
          </p>

          <p>
            <a href="<?= htmlspecialchars(autoUrl("settings/stripe/register")) ?>" class="btn btn-primary">Get started</a>
          </p>

          <p>
            We'll send you to Stripe and ask you to sign in or create an account. Find out <a href="https://stripe.com/gb/payments" target="_blank">more about Stripe</a> and <a href="https://stripe.com/gb/pricing" target="_blank">their pricing</a>.
          </p>

        <?php } ?>

      </main>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();
