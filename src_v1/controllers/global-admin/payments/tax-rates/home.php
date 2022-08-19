<?php

$db = app()->db;

$getTaxRates = $db->query("SELECT `ID`, `Name`, `Type`, `Region`, `Rate`, `InclusiveExclusive` FROM `tenantPaymentTaxRates` ORDER BY `Name` ASC;");
$taxRate = $getTaxRates->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Tax Rates - Payments - Admin Dashboard - SCDS";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments')) ?>">Pay</a></li>
      <li class="breadcrumb-item active" aria-current="page">Tax Rates</li>
    </ol>
  </nav>

  <h1>
    Tax Rates
  </h1>
  <p class="lead">Automatic subscription and billing systems.</p>

  <div class="row">
    <div class="col-lg-12">

      <?php if (isset($_SESSION['TaxRateAddSuccess'])) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>New tax rate added successfully</strong>
          </p>
        </div>
      <?php unset($_SESSION['TaxRateAddSuccess']);
      } ?>

      <p>
        <a class="btn btn-primary" href="<?= htmlspecialchars(autoUrl('admin/payments/tax-rates/new')) ?>">New Tax Rate</a>
      </p>

      <?php if ($taxRate) { ?>
        <div class="list-group">
          <?php do { ?>
            <div class="list-group-item">
              <p class="mb-0">
                <strong><?= htmlspecialchars($taxRate['Name']) ?></strong> (<?= htmlspecialchars($taxRate['Type']) ?>)
              </p>
              <p class="mb-0">
              <?= htmlspecialchars($taxRate['Rate']) ?>%, <?= htmlspecialchars($taxRate['InclusiveExclusive']) ?>
              </p>
            </div>
          <?php } while ($taxRate = $getTaxRates->fetch(PDO::FETCH_ASSOC)); ?>
        </div>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>No tax rates available</strong>
          </p>
          <p class="mb-0">
            Please <a class="alert-link" href="<?= htmlspecialchars(autoUrl('admin/payments/tax-rates/new')) ?>">add a tax rate</a>.
          </p>
        </div>
      <?php } ?>
    </div>
  </div>


</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();
