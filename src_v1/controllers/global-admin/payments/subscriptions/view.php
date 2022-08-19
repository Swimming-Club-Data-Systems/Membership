<?php

use function GuzzleHttp\json_encode;

$db = app()->db;

$getSubscription = $db->prepare("SELECT tenantPaymentSubscriptions.ID, PaymentMethod, Memo, Footer, StartDate, EndDate, Active, tenants.Name, tenants.ID Tenant FROM tenantPaymentSubscriptions INNER JOIN tenantStripeCustomers ON tenantStripeCustomers.CustomerID = tenantPaymentSubscriptions.Customer INNER JOIN tenants ON tenants.ID = tenantStripeCustomers.Tenant WHERE tenantPaymentSubscriptions.ID = ?");
$getSubscription->execute([
  $id
]);
$subscription = $getSubscription->fetch(PDO::FETCH_ASSOC);

if (!$subscription) halt(404);

$getPaymentMethods = $db->prepare("SELECT `TypeData`, `MethodID`, `Type`, `AcceptanceData`, `MethodDetails` FROM `tenantPaymentMethods` INNER JOIN `tenantStripeCustomers` ON tenantPaymentMethods.Customer = tenantStripeCustomers.CustomerID LEFT JOIN tenantPaymentMandates ON tenantPaymentMandates.PaymentMethod = tenantPaymentMethods.MethodID WHERE `Usable` AND `Tenant` = ?");
$getPaymentMethods->execute([
  $subscription['Tenant'],
]);
$paymentMethod = $getPaymentMethods->fetch(PDO::FETCH_ASSOC);

$getPlans = $db->prepare("SELECT tenantPaymentProducts.ID Product, tenantPaymentProducts.Name ProductName, tenantPaymentPlans.ID Plan, tenantPaymentPlans.Name PlanName, tenantPaymentPlans.PricePerUnit, tenantPaymentPlans.Currency, tenantPaymentSubscriptionProducts.Quantity, tenantPaymentSubscriptionProducts.TaxRate, tenantPaymentSubscriptionProducts.Discount, tenantPaymentSubscriptionProducts.DiscountType FROM tenantPaymentSubscriptionProducts INNER JOIN tenantPaymentPlans ON tenantPaymentSubscriptionProducts.Plan = tenantPaymentPlans.ID INNER JOIN tenantPaymentProducts ON tenantPaymentPlans.Product = tenantPaymentProducts.ID WHERE tenantPaymentSubscriptionProducts.Subscription = ? ORDER BY tenantPaymentProducts.Name ASC, tenantPaymentPlans.Name ASC");
$getPlans->execute([
  $id,
]);

$products = [];
while ($plan = $getPlans->fetch(PDO::FETCH_ASSOC)) {
  if (!isset($products[$plan['Product']])) {
    $products[$plan['Product']] = [
      'id' => $plan['Product'],
      'name' => $plan['ProductName'],
      'plans' => []
    ];
  }

  $products[$plan['Product']]['plans'][$plan['Plan']] = [
    'id' => $plan['Plan'],
    'name' => $plan['PlanName'],
    'price_per_unit' => (int) $plan['PricePerUnit'],
    'currency' => $plan['Currency'],
    'quantity' => (int) $plan['Quantity'],
    'tax_rate' => $plan['TaxRate'],
    'discount' => null,
  ];
}

$json = json_encode([
  'products' => $products
]);

$getProducts = $db->query("SELECT `ID`, `Name`, `Description`, `Updated` FROM `tenantPaymentProducts` ORDER BY `Name` ASC;");

$pagetitle = "Edit Subscription - Payments - Admin Dashboard - SCDS";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments')) ?>">Pay</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments/subscriptions')) ?>">Subscriptions</a></li>
      <li class="breadcrumb-item active" aria-current="page">View</li>
    </ol>
  </nav>

  <h1>
    <?= htmlspecialchars('Subscription ' . $id) ?>
  </h1>
  <p class="lead">Edit subscription.</p>

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['SubscriptionSaveError'])) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>There was a problem trying to save the subscription</strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($_SESSION['SubscriptionSaveError']) ?>
          </p>
        </div>
      <?php unset($_SESSION['SubscriptionSaveError']);
      } ?>

      <form method="post" class="needs-validation" novalidate>

        <?= \SCDS\CSRF::write() ?>

        <p>
          <strong><?= htmlspecialchars($subscription['Name']) ?></strong>
        </p>

        <div class="mb-3">
          <p class="mb-2">
            Products and plans
          </p>

          <div id="subscription-plans-box" class="mb-3"></div>

          <p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-plan-modal">
              Add a product plan
            </button>
          </p>
        </div>

        <input type="hidden" name="subscription-plans-object" id="subscription-plans-object" value="<?= htmlspecialchars($json) ?>">

        <div class="mb-3">
          <label class="form-label" for="subscription-payment-method">Payment method</label>
          <select name="subscription-payment-method" id="subscription-payment-method" class="form-select" required>
            <?php if ($paymentMethod) { ?>
              <?php

              do {

                $json = json_decode($paymentMethod['TypeData']);
                $methodDetails = null;
                if ($paymentMethod['MethodDetails']) $methodDetails = json_decode($paymentMethod['MethodDetails']);
                $title = htmlspecialchars('Payment method ' . $paymentMethod['MethodID']);
                switch ($paymentMethod['Type']) {
                  case 'card':
                    $title = htmlspecialchars(mb_convert_case($json->brand, MB_CASE_TITLE)) . ' ' . htmlspecialchars($json->funding) . ' card ' . htmlspecialchars($json->last4);
                    break;
                  case 'bacs_debit':
                    $title = htmlspecialchars('Direct Debit Ref: ' . ' ' . $methodDetails->bacs_debit->reference);
                    break;
                }

              ?>
                <option <?php if ($subscription['PaymentMethod'] == $paymentMethod['MethodID']) { ?>selected<?php } ?> value="<?= htmlspecialchars($paymentMethod['MethodID']) ?>"><?= $title ?></option>
              <?php } while ($paymentMethod = $getPaymentMethods->fetch(PDO::FETCH_ASSOC)); ?>
            <?php } else { ?>
              <option value="" disabled selected>No payment methods available</option>
            <?php } ?>
          </select>
          <div class="invalid-feedback">
            Select a payment method to use
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="subscription-invoice-memo">Invoice memo</label>
          <textarea class="form-control" name="subscription-invoice-memo" id="subscription-invoice-memo" rows="4"><?php if ($subscription['Memo']) { ?><?= htmlspecialchars($subscription['Memo']) ?><?php } ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label" for="subscription-invoice-footer">Invoice footer</label>
          <textarea class="form-control" name="subscription-invoice-footer" id="subscription-invoice-footer" rows="4"><?php if ($subscription['Footer']) { ?><?= htmlspecialchars($subscription['Footer']) ?><?php } ?></textarea>
        </div>

        <p>
          <button type="submit" class="btn btn-primary">
            Save subscription
          </button>
        </p>
      </form>

    </div>
  </div>

</div>

<!-- Modal -->
<div class="modal fade" id="add-plan-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="add-plan-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="add-plan-modal-label">Add plan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
        <form id="add-plan-form" class="needs-validation" novalidate>

          <?= \SCDS\CSRF::write() ?>

          <div class="mb-3">
            <label class="form-label" for="product-select">Product</label>
            <select name="product-select" id="product-select" class="form-select" required data-plans-ajax-url="<?= htmlspecialchars(autoUrl('admin/payments/subscriptions/new/get-product-plans')) ?>">
              <option value="" selected disabled>Select a product</option>
              <?php while ($product = $getProducts->fetch(PDO::FETCH_ASSOC)) { ?>
                <option id="<?= htmlspecialchars('product-select-' . $product['ID']) ?>" value="<?= htmlspecialchars($product['ID']) ?>" data-name="<?= htmlspecialchars($product['Name']) ?>"><?= htmlspecialchars($product['Name']) ?></option>
              <?php } ?>
            </select>
            <div class="invalid-feedback">
              You must select a product
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="plan-select">Plan</label>
            <select name="plan-select" id="plan-select" class="form-select" required>
              <option value="" selected disabled>Select a plan</option>
            </select>
            <div class="invalid-feedback">
              You must select a plan for this product
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="plan-quantity">Quantity</label>
            <input type="number" name="plan-quantity" id="plan-quantity" class="form-control" min="1" step="1" required value="1">
            <div class="invalid-feedback">
              You must enter a valid quantity
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" form="add-plan-form" class="btn btn-primary">Add plan</button>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->addJS("js/numerical/bignumber.min.js");
$footer->addJS("js/global-admin/payments/subscriptions/edit.js", true);
$footer->addJs('js/NeedsValidation.js');
$footer->render();
