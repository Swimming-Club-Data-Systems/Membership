<?php

$db = app()->db;

$getInvoices = $db->prepare("SELECT tenantPaymentInvoices.ID, tenants.Name FROM `tenantPaymentInvoices` INNER JOIN tenantStripeCustomers ON tenantPaymentInvoices.Customer = tenantStripeCustomers.CustomerID INNER JOIN tenants ON tenantStripeCustomers.Tenant = tenants.ID ORDER BY `Name` ASC;");
$getInvoices->execute([
  'today' => (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y-m-d'),
]);
$invoice = $getInvoices->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Invoices - Payments - Admin Dashboard - SCDS";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments')) ?>">Pay</a></li>
      <li class="breadcrumb-item active" aria-current="page">Invoices</li>
    </ol>
  </nav>

  <h1>
    Invoices
  </h1>
  <p class="lead">View or create invoices.</p>

  <p>
    <a href="<?= htmlspecialchars(autoUrl('admin/payments/invoices/new')) ?>" class="btn btn-primary">
      New invoice
    </a>
  </p>

  <?php if ($invoice) { ?>
    <div class="list-group">
      <?php do { ?>
        <a href="<?= htmlspecialchars(autoUrl('admin/payments/invoices/' . $invoice['ID'])) ?>" class="list-group-item list-group-item-action">
          <p class="mb-0">
            <strong><?= htmlspecialchars($invoice['ID']) ?></strong>
          </p>
          <p class="mb-0">
            Invoice <?= htmlspecialchars($invoice['ID']) ?>
          </p>
        </a>
      <?php } while ($invoice = $getInvoices->fetch(PDO::FETCH_ASSOC)); ?>
    </div>
  <?php } else { ?>
    <div class="alert alert-warning">
      <p class="mb-0">
        <strong>There are no invoices to show</strong>
      </p>
      <p class="mb-0">
        Create an <a href="<?= htmlspecialchars(autoUrl('admin/payments/invoices/new')) ?>" class="alert-link">invoice</a>
      </p>
    </div>
  <?php } ?>


</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();
