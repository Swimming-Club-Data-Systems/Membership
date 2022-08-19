<?php

$pagetitle = "Subscriptions - Payments - SCDS";

$db = app()->db;
$tenant = app()->adminCurrentTenant;
$user = app()->adminCurrentUser;

$getSubscriptions = $db->prepare("SELECT tenantPaymentSubscriptions.ID, tenants.Name FROM `tenantPaymentSubscriptions` INNER JOIN tenantStripeCustomers ON tenantPaymentSubscriptions.Customer = tenantStripeCustomers.CustomerID INNER JOIN tenants ON tenantStripeCustomers.Tenant = tenants.ID WHERE tenantStripeCustomers.Tenant = :tenant AND tenantPaymentSubscriptions.Active AND (EndDate IS NULL OR EndDate >= :today) ORDER BY `Name` ASC;");
$getSubscriptions->execute([
  'tenant' => $tenant->getId(),
  'today' => (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y-m-d'),
]);
$subscription = $getSubscriptions->fetch(PDO::FETCH_ASSOC);

include BASE_PATH . "views/root/head.php";

?>

<div class="container-xl">

  <?php include BASE_PATH . 'controllers/admin-tools/scds-payments/admin/nav.php'; ?>

  <div class="row align-items-center">
    <div class="col-auto">
      <div class="bg-primary text-white p-4 my-4 d-inline-block rounded">
        <h1 class="">Subscriptions</h1>
        <p class="mb-0">View your organisation's current subscriptions</p>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8">

      <?php if ($subscription) { ?>
        <div class="list-group">
          <?php do { ?>
            <a href="<?= htmlspecialchars(autoUrl('payments-admin/subscriptions/' . $subscription['ID'])) ?>" class="list-group-item list-group-item-action">
              <p class="mb-0">
                <strong>Subscription <?= htmlspecialchars($subscription['ID']) ?></strong>
              </p>
            </a>
          <?php } while ($subscription = $getSubscriptions->fetch(PDO::FETCH_ASSOC)); ?>
        </div>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>There are no current or upcoming subscriptions</strong>
          </p>
          <p class="mb-0">
            Speak to SCDS if you expected to see something here
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