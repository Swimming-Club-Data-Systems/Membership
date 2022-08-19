<?php

$db = app()->db;
$tenant = app()->tenant;

$fluidContainer = true;

$pagetitle = "Payment Methods for Club and Swim England Membership Fees";

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">
  <div class="row justify-content-between">
    <aside class="col-md-3 d-none d-md-block">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/settings/SettingsLinkGroup.json'));
      echo $list->render('settings-fees');
      ?>
    </aside>
    <div class="col-md-9">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('settings')) ?>">Settings</a></li>
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('settings/fees')) ?>">Fees</a></li>
          <li class="breadcrumb-item active" aria-current="page">Annual Payment Methods</li>
        </ol>
      </nav>

      <main>
        <h1>Payment Methods for Club and Swim England Membership Fees</h1>
        <p class="lead">Select available payment methods for annual membership fees</p>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success']) && $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success']) { ?>
          <div class="alert alert-success">Changes saved successfully</div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success']);
        } ?>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']) && $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']) { ?>
          <div class="alert alert-danger">Changes could not be saved</div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']);
        } ?>

        <form method="post">

          <p>
            By default, card and direct debit options are enabled, if you have set up your Stripe connection.
          </p>

          <p>
            By default, the direct debit option is enabled if you have set up a legacy GoCardless connection.
          </p>

          <div class="mb-3">
            <div class="form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="MEMBERSHIP_FEE_PM_CARD" name="MEMBERSHIP_FEE_PM_CARD" <?php if ($tenant->getBooleanKey('MEMBERSHIP_FEE_PM_CARD') && $tenant->getStripeAccount()) { ?>checked<?php } ?> <?php if (!$tenant->getStripeAccount()) { ?>disabled<?php } ?>>
              <label class="form-check-label" for="MEMBERSHIP_FEE_PM_CARD">Credit/debit card payments</label>
            </div>
          </div>

          <div class="mb-3">
            <div class="form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="MEMBERSHIP_FEE_PM_DD" name="MEMBERSHIP_FEE_PM_DD" <?php if ($tenant->getBooleanKey('MEMBERSHIP_FEE_PM_DD') && ($tenant->getStripeAccount() || $tenant->getGoCardlessAccessToken()) && $tenant->getKey('USE_DIRECT_DEBIT')) { ?>checked<?php } ?> <?php if (!$tenant->getStripeAccount() && !$tenant->getGoCardlessAccessToken()) { ?>disabled<?php } ?>>
              <label class="form-check-label" for="MEMBERSHIP_FEE_PM_DD">Direct debit</label>
            </div>
          </div>

          <p>
            Support for the following payment methods is coming soon;
          </p>

          <div class="mb-3">
            <div class="form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="MEMBERSHIP_FEE_PM_BACS" name="MEMBERSHIP_FEE_PM_BACS" <?php if ($tenant->getBooleanKey('MEMBERSHIP_FEE_PM_BACS')) { ?>checked<?php } ?> disabled>
              <label class="form-check-label" for="MEMBERSHIP_FEE_PM_BACS">Bank transfer</label>
            </div>
          </div>

          <div class="mb-3">
            <div class="form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="MEMBERSHIP_FEE_PM_CASH" name="MEMBERSHIP_FEE_PM_CASH" <?php if ($tenant->getBooleanKey('MEMBERSHIP_FEE_PM_CASH')) { ?>checked<?php } ?> disabled>
              <label class="form-check-label" for="MEMBERSHIP_FEE_PM_CASH">Cash</label>
            </div>
          </div>

          <div class="mb-3">
            <div class="form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="MEMBERSHIP_FEE_PM_CHEQUE" name="MEMBERSHIP_FEE_PM_CHEQUE" <?php if ($tenant->getBooleanKey('MEMBERSHIP_FEE_PM_CHEQUE')) { ?>checked<?php } ?> disabled>
              <label class="form-check-label" for="MEMBERSHIP_FEE_PM_CHEQUE">Cheque</label>
            </div>
          </div>

          <p>
            If no payment methods are enabled, your users will not be able to finish registration of renewal.
          </p>

          <p>
            <button class="btn btn-success" type="submit">
              Save
            </button>
          </p>
        </form>
      </main>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();
