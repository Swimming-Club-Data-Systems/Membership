<?php

$pagetitle = "Payment Cards - Payments - SCDS";

$db = app()->db;
$tenant = app()->adminCurrentTenant;
$user = app()->adminCurrentUser;

$getPaymentMethods = $db->prepare("SELECT tenantPaymentMethods.ID, `MethodID`, `MandateID`, `Type`, `TypeData`, `AcceptanceData`, `MethodDetails`, `Usable`, `Status` FROM tenantPaymentMethods LEFT JOIN tenantPaymentMandates ON tenantPaymentMandates.PaymentMethod = tenantPaymentMethods.MethodID INNER JOIN tenantStripeCustomers ON tenantPaymentMethods.Customer = tenantStripeCustomers.CustomerID WHERE Tenant = ? AND `Type` = 'card' AND `Usable` ORDER BY Usable DESC, tenantPaymentMethods.Created DESC");
$getPaymentMethods->execute([
  $tenant->getId(),
]);
$paymentMethod = $getPaymentMethods->fetch(PDO::FETCH_ASSOC);

$default = $tenant->getKey('DEFAULT_PAYMENT_MANDATE');

include BASE_PATH . "views/root/head.php";

?>

<div class="container-xl">

  <?php include BASE_PATH . 'controllers/admin-tools/scds-payments/admin/nav.php'; ?>

  <div class="row align-items-center">
    <div class="col-auto">
      <div class="bg-primary text-white p-4 my-4 d-inline-block rounded">
        <h1 class="">Payment Cards</h1>
        <p class="mb-0">Add and edit your payment cards</p>
      </div>
    </div>
    <div class="d-none d-sm-flex col-sm-auto ms-auto">
      <p class="mb-0">
        <img class="apple-pay-row" src="<?= autoUrl("img/stripe/apple-pay-mark.svg", false) ?>" aria-hidden="true"> <img class="google-pay-row" src="<?= autoUrl("img/stripe/google-pay-mark.svg", false) ?>" aria-hidden="true"> <img class="visa-row" src="<?= autoUrl("img/stripe/visa.svg", false) ?>" aria-hidden="true"> <img class="mastercard-row" src="<?= autoUrl("img/stripe/mastercard.svg", false) ?>" aria-hidden="true"> <img class="amex-row" src="<?= autoUrl("img/stripe/amex.svg", false) ?>" aria-hidden="true">
      </p>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8">

      <p>
        <a href="<?= htmlspecialchars(autoUrl('payments-admin/payment-cards/add')) ?>" class="btn btn-primary">
          Add a payment card
        </a>
      </p>

      <?php if ($paymentMethod) { ?>
        <div class="list-group">
          <?php do {
            $typeData = json_decode($paymentMethod['TypeData']);
          ?>
            <a href="<?= htmlspecialchars(autoUrl('payments-admin/payment-methods/' . $paymentMethod['ID'])) ?>" class="list-group-item list-group-item-action">
              <div class="row align-items-center mb-2">
                <div class="col-auto">
                  <img src="<?= htmlspecialchars(autoUrl("img/stripe/" . $typeData->brand . ".png", false)) ?>" srcset="<?= htmlspecialchars(autoUrl("img/stripe/" . $typeData->brand . "@2x.png", false)) ?> 2x, <?= htmlspecialchars(autoUrl("img/stripe/" . $typeData->brand . "@3x.png", false)) ?> 3x" style="width:40px;"> <span class="visually-hidden"><?= htmlspecialchars(getCardBrand($typeData->brand)) ?></span>
                </div>
                <div class="col-auto">
                  <h2 class="h1 my-0">
                    &#0149;&#0149;&#0149;&#0149; <?= htmlspecialchars($typeData->last4) ?>
                  </h2>
                </div>
              </div>
              <p class="lead">
                <?= htmlspecialchars(mb_convert_case($typeData->funding, MB_CASE_TITLE)) ?> card
              </p>

              <p class="mb-0">
                <span class="text-link-color">
                  Edit card
                </span>
              </p>
            </a>
          <?php } while ($paymentMethod = $getPaymentMethods->fetch(PDO::FETCH_ASSOC)); ?>
        </div>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>Your organisation has no payment cards available</strong>
          </p>
        </div>
      <?php } ?>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();

?>