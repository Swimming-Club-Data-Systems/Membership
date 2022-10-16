<?php

$pagetitle = "Payments - Admin Dashboard - SCDS";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item active" aria-current="page">Pay</li>
    </ol>
  </nav>

  <h1>
    Payments
  </h1>
  <p class="lead">Automatic subscription and billing systems.</p>

  <div class="list-group">
    <a href="<?= htmlspecialchars(autoUrl('admin/payments/products')) ?>" class="list-group-item list-group-item-action">Products</a>
    <a href="<?= htmlspecialchars(autoUrl('admin/payments/subscriptions')) ?>" class="list-group-item list-group-item-action">Subscriptions</a>
    <a href="<?= htmlspecialchars(autoUrl('admin/payments/tax-rates')) ?>" class="list-group-item list-group-item-action">Tax Rates</a>
    <a href="<?= htmlspecialchars(autoUrl('admin/payments/invoices')) ?>" class="list-group-item list-group-item-action">Invoices</a>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();
