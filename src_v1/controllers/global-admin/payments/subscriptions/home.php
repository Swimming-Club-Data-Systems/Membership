<?php

$db = app()->db;

$getSubscriptions = $db->prepare("SELECT tenantPaymentSubscriptions.ID, tenants.Name FROM `tenantPaymentSubscriptions` INNER JOIN tenantStripeCustomers ON tenantPaymentSubscriptions.Customer = tenantStripeCustomers.CustomerID INNER JOIN tenants ON tenantStripeCustomers.Tenant = tenants.ID WHERE tenantPaymentSubscriptions.Active AND (EndDate IS NULL OR EndDate >= :today) ORDER BY `Name` ASC;");
$getSubscriptions->execute([
  'today' => (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y-m-d'),
]);
$subscription = $getSubscriptions->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Subscriptions - Payments - Admin Dashboard - SCDS";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments')) ?>">Pay</a></li>
      <li class="breadcrumb-item active" aria-current="page">Subscriptions</li>
    </ol>
  </nav>

  <h1>
    Subscriptions
  </h1>
  <p class="lead">Automatic subscription and billing systems.</p>

  <p>
    <a href="<?= htmlspecialchars(autoUrl('admin/payments/subscriptions/new')) ?>" class="btn btn-primary">
      New subscription
    </a>
  </p>

  <?php if ($subscription) { ?>
  <div class="list-group">
    <?php do { ?>
      <a href="<?= htmlspecialchars(autoUrl('admin/payments/subscriptions/' . $subscription['ID'])) ?>" class="list-group-item list-group-item-action">
        <p class="mb-0">
          <strong><?= htmlspecialchars($subscription['Name']) ?></strong>
        </p>
        <p class="mb-0">
          Subscription <?= htmlspecialchars($subscription['ID']) ?>
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
        Create a <a href="<?= htmlspecialchars(autoUrl('admin/payments/subscriptions/new')) ?>" class="alert-link">new subscription</a>
      </p>
    </div>
  <?php } ?>


</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();
