<?php

$pagetitle = "Mandate - Payments - SCDS";

$db = app()->db;
$tenant = app()->adminCurrentTenant;
$user = app()->adminCurrentUser;

$getMandates = $db->prepare("SELECT tenantPaymentMethods.ID, `MethodID`, `MandateID`, `Type`, `TypeData`, `AcceptanceData`, `MethodDetails`, `Usable`, `Status` FROM tenantPaymentMethods INNER JOIN tenantPaymentMandates ON tenantPaymentMandates.PaymentMethod = tenantPaymentMethods.MethodID INNER JOIN tenantStripeCustomers ON tenantPaymentMethods.Customer = tenantStripeCustomers.CustomerID WHERE Tenant = ? AND `Type` = 'bacs_debit' ORDER BY Usable DESC, tenantPaymentMethods.Created DESC");
$getMandates->execute([
  $tenant->getId(),
]);

$getUsableMandates = $db->prepare("SELECT tenantPaymentMethods.ID, `MethodID`, `MandateID`, `Type`, `TypeData`, `AcceptanceData`, `MethodDetails`, `Usable`, `Status` FROM tenantPaymentMethods INNER JOIN tenantPaymentMandates ON tenantPaymentMandates.PaymentMethod = tenantPaymentMethods.MethodID INNER JOIN tenantStripeCustomers ON tenantPaymentMethods.Customer = tenantStripeCustomers.CustomerID WHERE Usable AND Tenant = ? AND `Type` = 'bacs_debit' ORDER BY tenantPaymentMethods.Created DESC");
$getUsableMandates->execute([
  $tenant->getId(),
]);

$default = $tenant->getKey('DEFAULT_PAYMENT_MANDATE');

include BASE_PATH . "views/root/head.php";

?>

<div class="container-xl">

  <?php include BASE_PATH . 'controllers/admin-tools/scds-payments/admin/nav.php'; ?>

  <div class="row align-items-center">
    <div class="col">
      <div class="bg-primary text-white p-4 my-4 d-inline-block rounded">
        <h1 class="">Direct Debit</h1>
        <p class="mb-0">Manage your Direct Debit Instruction</p>
      </div>
    </div>
    <div class="d-none d-sm-flex col-sm-auto ms-auto">
      <img style="max-height:50px;" src="<?= htmlspecialchars(autoUrl("img/directdebit/directdebit.png", false)) ?>" srcset="<?= htmlspecialchars(autoUrl("img/directdebit/directdebit@2x.png", false)) ?> 2x, <?= htmlspecialchars(autoUrl("img/directdebit/directdebit@3x.png", false)) ?> 3x" alt="Direct Debit Logo">
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8">
      <p>
        <a href="<?= htmlspecialchars(autoUrl('payments-admin/direct-debit-instruction/set-up')) ?>" class="btn btn-primary">
          Set up a DDI
        </a>
      </p>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['Saved_DD_Default']) && $_SESSION['Saved_DD_Default']) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>We've saved your changes to your default direct debit</strong>
          </p>
        </div>
      <?php unset($_SESSION['Saved_DD_Default']);
      } ?>

      <?php if (isset($_SESSION['StripeDDSuccess']) && $_SESSION['StripeDDSuccess']) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>We've set up your new direct debit</strong>
          </p>
          <p>
            It will take a few days for the mandate to be confirmed at your bank.
          </p>

          <p class="mb-0 small">
            At busy times, your mandate may take a few minutes to appear in our systems.
          </p>
        </div>
      <?php unset($_SESSION['StripeDDSuccess']);
      } ?>

      <?php if ($mandate = $getMandates->fetch(PDO::FETCH_ASSOC)) { ?>

        <ul class="list-group">
          <?php do {
            $json = json_decode($mandate['TypeData']);
            $acceptance = json_decode($mandate['AcceptanceData']);
            $methodDetails = json_decode($mandate['MethodDetails']);
          ?>
            <li class="list-group-item" id="<?= htmlspecialchars('mandate-' . $mandate['ID']) ?>">
              <h2><?= htmlspecialchars(implode("-", str_split($json->sort_code, 2))) ?> - &middot;&middot;&middot;&middot;<?= htmlspecialchars($json->last4) ?><?php if ($default == $mandate['ID']) { ?> <span class="badge bg-success">Default</span><?php } ?></h2>

              <dl class="row">
                <dt class="col-sm-3">Sort code</dt>
                <dd class="col-sm-9 font-monospace"><?= htmlspecialchars(implode("-", str_split($json->sort_code, 2))) ?></dd>

                <dt class="col-sm-3">Account number</dt>
                <dd class="col-sm-9 font-monospace">&middot;&middot;&middot;&middot;<?= htmlspecialchars($json->last4) ?></dd>

                <dt class="col-sm-3">Payment reference</dt>
                <dd class="col-sm-9 font-monospace"><?= htmlspecialchars($methodDetails->bacs_debit->reference) ?></dd>

                <dt class="col-sm-3">Active</dt>
                <dd class="col-sm-9"><?php if ($mandate['Status'] == 'active') { ?>Yes<?php } else { ?>No<?php } ?></dd>
              </dl>

              <p class="mb-0">
                <a href="<?= htmlspecialchars($methodDetails->bacs_debit->url) ?>" target="_blank">
                  View Direct Debit Instruction
                </a>
              </p>
            </li>
          <?php } while ($mandate = $getMandates->fetch(PDO::FETCH_ASSOC)); ?>
        </ul>

      <?php } else { ?>
        <div class="alert alert-info">
          <p class="mb-0">
            <strong>Your organisation does not have a Direct Debit Instruction set up</strong>
          </p>
        </div>
      <?php } ?>

    </div>
    <div class="col">
      <div class="card">
        <div class="card-header">
          Default payment method
        </div>
        <div class="card-body">
          <form method="post">
            <div class="mb-3">
              <select class="form-select" id="default-mandate" name="default-mandate">
                <option <?php if ($default == null) { ?>selected<?php } ?> disabled value="nothing">Select a default mandate</option>
                <?php while ($mandate = $getUsableMandates->fetch(PDO::FETCH_ASSOC)) {
                  $json = json_decode($mandate['TypeData']);
                ?>
                  <option <?php if ($default == $mandate['ID']) { ?>selected<?php } ?> value="<?= htmlspecialchars($mandate['ID']) ?>">
                    <?= htmlspecialchars($methodDetails->bacs_debit->reference) ?> (Account ending <?= htmlspecialchars($json->last4) ?>)
                  </option>
                <?php } ?>
              </select>
            </div>

            <?= \SCDS\CSRF::write() ?>

            <p class="mb-0">
              <button type="submit" class="btn btn-primary">Save</button>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();

?>