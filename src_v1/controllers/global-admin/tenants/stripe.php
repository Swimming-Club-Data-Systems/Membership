<?php

$db = app()->db;
$getClubs = $db->prepare("SELECT `ID`, `Name`, `Code`, `Verified` FROM tenants WHERE `UniqueID` = ? ORDER BY `Name` ASC");
$getClubs->execute([
  $id
]);
$club = $getClubs->fetch(PDO::FETCH_ASSOC);

if (!$club) halt(404);

$tenant = Tenant::fromId($club['ID']);

\Stripe\Stripe::setApiKey(getenv('STRIPE'));
$at = $tenant->getStripeAccount();

$stripeAccount = \Stripe\Account::retrieve($at);

$supportsDirectDebit = isset($stripeAccount->capabilities->bacs_debit_payments) && $stripeAccount->capabilities->bacs_debit_payments == 'active';

$countries = getISOAlpha2Countries();

$pagetitle = "Stripe Settings - " . htmlspecialchars($tenant->getName());

$phone = null;
if (isset($stripeAccount->business_profile->support_phone)) {
  $phone = $stripeAccount->business_profile->support_phone;
}
try {
  $number = \Brick\PhoneNumber\PhoneNumber::parse((string) $phone);
  $phone = $number->formatForCallingFrom('GB');
} catch (Exception $e) {
}

$applePay = \Stripe\ApplePayDomain::all([
  'limit' => 20
], [
  'stripe_account' => $tenant->getStripeAccount()
]);

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <div class="row justify-content-center py-3">
    <div class="col-lg-8 col-md-10">
      <div class="bg-primary text-white p-4 mb-4 d-inline-block rounded">
        <h1 class="">Stripe Settings</h1>
        <p class="mb-0"><?= htmlspecialchars($club['Name']) ?></p>
      </div>

      <?php if ($at) { ?>

        <p>
          Stripe account (<span class="font-monospace"><?= htmlspecialchars($at) ?></span>) for <?= htmlspecialchars($club['Name']) ?> is currently connected.
        </p>

        <h2>
          Business details
        </h2>

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

        <h2>
          Apple Pay Domains
        </h2>

        <p>
          Domains must be registered for Apple Pay with Stripe ahead of use. Domains must be registered in LIVE MODE.
        </p>

        <h3>Current Domains</h3>

        <p>
          The following domains are set up for Apple Pay.
        </p>

        <?php if ($applePay->object == 'list' && sizeof($applePay->data) > 0) { ?>
          <ul class="list-group mb-3">
            <?php foreach ($applePay->data as $index => $domain) { ?>
              <li class="list-group-item">
                <div>
                  <?= htmlspecialchars($domain->domain_name) ?>
                </div>
                <div>
                  <?= htmlspecialchars($domain->id) ?>
                </div>
                <div>
                  Created at <?= htmlspecialchars((new DateTime('@' . $domain->created, new DateTimeZone('UTC')))->format('Y-m-d H:i:s')) ?>
                </div>
                <div>
                  <?= htmlspecialchars($domain->live_mode) ?>
                </div>

                <div class="mt-3">
                  <a href="<?= htmlspecialchars(autoUrl("admin/tenants/$id/stripe/delete-apple-pay-domain?id=" . urlencode($domain->id))) ?>" class="btn btn-danger">Delete domain</a>
                </div>
              </li>
            <?php } ?>
          </ul>
        <?php } ?>

        <form action="<?= htmlspecialchars(autoUrl("admin/tenants/$id/stripe/add-apple-pay-domain")) ?>" method="post" class="needs-validation" novalidate>
          <h3>Add a new Apple Pay Domain for <?= htmlspecialchars($club['Name']) ?></h3>

          <?php if (getenv('IS_DEV')) { ?>
            <div class="alert alert-info">
              <p class="mb-0">
              <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <strong>You must be in Live Mode to register a domain</strong>
              </p>
            </div>
          <?php } ?>

          <div class="mb-3">
            <label class="form-label" for="apple-pay-domain">Domain name</label>
            <input class="form-control" type="text" name="apple-pay-domain" id="apple-pay-domain" required placeholder="example.com" <?php if (getenv('IS_DEV')) { ?>disabled<?php } ?>>
          </div>

          <p>
            <button class="btn btn-primary" <?php if (getenv('IS_DEV')) { ?>disabled<?php } ?>>
              Add domain
            </button>
          </p>
        </form>

      <?php } else { ?>

        <p>
          <?= htmlspecialchars($club['Name']) ?> has no active Stripe account connected.
        </p>

      <?php } ?>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->addJs('js/NeedsValidation.js');
$footer->render();
