<?php

$db = app()->db;

$invoice = TenantPayments\Invoice::get($id);
if (!$invoice) halt(404);

$pagetitle = "View Invoice - Payments - Admin Dashboard - SCDS";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments')) ?>">Pay</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments/invoices')) ?>">Invoices</a></li>
      <li class="breadcrumb-item active" aria-current="page">View</li>
    </ol>
  </nav>

  <h1>
    <?= htmlspecialchars('Invoice ' . $id) ?>
  </h1>
  <p class="lead"><?= htmlspecialchars($invoice->getReference() . ', issued ' . $invoice->getDate()->format('j F Y')) ?></p>

  <?php if (isset($_SESSION['InvoiceAddSuccess'])) { ?>
    <div class="alert alert-success">
      <p class="mb-0">
        <strong>Invoice added successfully</strong>
      </p>
    </div>
  <?php unset($_SESSION['InvoiceAddSuccess']);
  } ?>

  <h2>
    Invoice items
  </h2>

  <table class="table">
    <thead>
      <tr>
        <th>
          Item
        </th>
        <th>
          Quantity
        </th>
        <th>
          VAT
        </th>
        <th>
          Amount
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($invoice->getItems() as $item) { ?>
        <tr>
          <td class="align-middle">
            <p class="mb-0"><strong><?= htmlspecialchars($item->getName()) ?></strong></p>
            <p class="mb-0"><?= htmlspecialchars($item->getDescription()) ?></p>
          </td>
          <td class="align-middle">
            <?= htmlspecialchars($item->getQuantity()) ?> &times; <?= htmlspecialchars($item->getFormattedPricePerUnit()) ?>
          </td>
          <td class="align-middle">
            <?= htmlspecialchars($item->getFormattedVatAmount()) ?>
          </td>
          <td class="align-middle">
            <?= htmlspecialchars($item->getFormattedAmount()) ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

  <h2>
    Invoice details
  </h2>

  <div class="row">
    <div class="col-lg-6">
      <div class="card card-body">
        <dl class="row mb-0">
          <dt class="col-sm-3">
            Subtotal
          </dt>
          <dd class="col-sm-9">
            <?= htmlspecialchars($invoice->getFormattedTotal()) ?>
          </dd>

          <dt class="col-sm-3">
            Value Added Tax
          </dt>
          <dd class="col-sm-9">
            <?= htmlspecialchars($invoice->getFormattedVatTotal()) ?>
          </dd>

          <dt class="col-sm-3">
            Total
          </dt>
          <dd class="col-sm-9 mb-0">
            <?= htmlspecialchars($invoice->getFormattedTotalWithVat()) ?>
          </dd>
        </dl>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card card-body">
        <dl class="row mb-0">
          <dt class="col-sm-3">
            Payment terms
          </dt>
          <dd class="col-sm-9">
            <?= htmlspecialchars($invoice->getPaymentTerms()) ?>
          </dd>

          <dt class="col-sm-3">
            How to pay
          </dt>
          <dd class="col-sm-9 mb-0">
            <?= htmlspecialchars($invoice->getHowToPay()) ?>
          </dd>
        </dl>
      </div>
    </div>
  </div>

  <?php pre($invoice); ?>

</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();
