<?php

$pagetitle = "Subscriptions - Payments - SCDS";

$db = app()->db;
$tenant = app()->adminCurrentTenant;
$user = app()->adminCurrentUser;

$getSubscription = $db->prepare("SELECT tenantPaymentSubscriptions.ID, PaymentMethod, tenantPaymentMethods.ID PMID, Memo, Footer, StartDate, EndDate, Active, tenants.Name, tenants.ID Tenant FROM tenantPaymentSubscriptions INNER JOIN tenantStripeCustomers ON tenantStripeCustomers.CustomerID = tenantPaymentSubscriptions.Customer INNER JOIN tenants ON tenants.ID = tenantStripeCustomers.Tenant INNER JOIN tenantPaymentMethods ON tenantPaymentMethods.MethodID = tenantPaymentSubscriptions.PaymentMethod WHERE tenantPaymentSubscriptions.ID = ?");
$getSubscription->execute([
  $id
]);
$subscription = $getSubscription->fetch(PDO::FETCH_ASSOC);

if (!$subscription) halt(404);

if ($subscription['Tenant'] != $tenant->getId()) halt(404);

$getPlans = $db->prepare("SELECT tenantPaymentProducts.ID Product, tenantPaymentProducts.Name ProductName, tenantPaymentPlans.ID Plan, tenantPaymentPlans.Name PlanName, tenantPaymentPlans.PricePerUnit, tenantPaymentPlans.Currency, tenantPaymentSubscriptionProducts.Quantity, tenantPaymentSubscriptionProducts.TaxRate, tenantPaymentSubscriptionProducts.Discount, tenantPaymentSubscriptionProducts.DiscountType, tenantPaymentPlans.UsageType, tenantPaymentPlans.BillingInterval FROM tenantPaymentSubscriptionProducts INNER JOIN tenantPaymentPlans ON tenantPaymentSubscriptionProducts.Plan = tenantPaymentPlans.ID INNER JOIN tenantPaymentProducts ON tenantPaymentPlans.Product = tenantPaymentProducts.ID WHERE tenantPaymentSubscriptionProducts.Subscription = ? ORDER BY tenantPaymentProducts.Name ASC, tenantPaymentPlans.Name ASC");
$getPlans->execute([
  $id,
]);
$plan = $getPlans->fetch(PDO::FETCH_ASSOC);

$formatter = new NumberFormatter(app()->locale, NumberFormatter::CURRENCY);

include BASE_PATH . "views/root/head.php";

?>

<div class="container-xl">

  <?php include BASE_PATH . 'controllers/admin-tools/scds-payments/admin/nav.php'; ?>

  <div class="row align-items-center">
    <div class="col-auto">
      <div class="bg-primary text-white p-4 my-4 d-inline-block rounded">
        <h1 class=""><?= htmlspecialchars('Subscription ' . $id) ?></h1>
        <p class="mb-0">View subscription</p>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8">

      <h2>Payment method</h2>
      <p>
        <a href="<?= htmlspecialchars(autoUrl('payments-admin/payment-methods/' . $subscription['PMID'])) ?>"><?= htmlspecialchars($subscription['PMID']) ?></a>
      </p>

      <h2>Subscription plans</h2>
      <?php if ($plan) { ?>
        <table class="table">
          <thead>
            <tr>
              <th>
                Product
              </th>
              <th>
                Quantity
              </th>
              <th>
                Total
              </th>
            </tr>
          </thead>
          <tbody>
            <?php do {
              $interval = new DateInterval($plan['BillingInterval']);
              $years = (int) $interval->format('%y');
              $months = (int) $interval->format('%m');
              $days = (int) $interval->format('%d');
              $intervalString = "";
              if ($years > 0) {
                $intervalString .= ' ' . $years . ' year';
                if ($years != 1) $intervalString .= 's';
              }
              if ($months > 0) {
                $intervalString .= ' ' . $months . ' month';
                if ($months != 1) $intervalString .= 's';
              }
              if ($days > 0) {
                $intervalString .= ' ' . $days . ' day';
                if ($days != 1) $intervalString .= 's';
              }
              $intervalString = trim($intervalString);
            ?>
              <tr>
                <td class="align-middle">
                  <p class="mb-0">
                    <strong><?= htmlspecialchars($plan['ProductName']) ?></strong>
                  </p>
                  <p class="mb-0">
                    <?= htmlspecialchars($plan['PlanName']) ?>
                  </p>
                </td>
                <td class="align-middle">
                  <?= htmlspecialchars($plan['Quantity']) ?>
                </td>
                <td class="align-middle">
                  <?= htmlspecialchars($formatter->formatCurrency((string) (\Brick\Math\BigDecimal::of((string) ($plan['PricePerUnit'] * $plan['Quantity'])))->withPointMovedLeft(2)->toScale(2), $plan['Currency'])) ?><?php if ($plan['UsageType'] == 'recurring') { ?>, Recurs every <?= htmlspecialchars($intervalString) ?><?php } ?>
                </td>
              </tr>
            <?php } while ($plan = $getPlans->fetch(PDO::FETCH_ASSOC)); ?>
          </tbody>
        </table>
      <?php } else { ?>
        <div class="alert alert-warning">
          <strong>There are no fees on this subscription</strong>
        </div>
      <?php } ?>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();

?>