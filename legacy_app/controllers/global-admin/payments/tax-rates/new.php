<?php

$pagetitle = "New Tax Rate - Payments - Admin Dashboard - SCDS";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments')) ?>">Pay</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments/tax-rates')) ?>">Tax Rates</a></li>
      <li class="breadcrumb-item active" aria-current="page">New</li>
    </ol>
  </nav>

  <h1>
    New Tax Rate
  </h1>
  <p class="lead">Add a new rate.</p>

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['TaxRateAddError'])) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>There was a problem trying to create the new tax rate</strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($_SESSION['TaxRateAddError']) ?>
          </p>
        </div>
      <?php unset($_SESSION['TaxRateAddError']);
      } ?>

      <form method="post" class="needs-validation" novalidate>

        <?= \SCDS\CSRF::write() ?>

        <div class="mb-3">
          <label class="form-label" for="rate-name">Name</label>
          <input type="text" name="rate-name" id="rate-name" required class="form-control">
          <div class="invalid-feedback">
            Provide a name for this tax rate
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="rate-type">Type</label>
          <select class="form-select" name="rate-type" id="rate-type" required>
            <option value="vat" selected>VAT (Value Added Tax)</option>
          </select>
          <div class="invalid-feedback">
            Select a tax type
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="rate-region">Region</label>
          <input type="text" name="rate-region" id="rate-region" required class="form-control">
          <div class="invalid-feedback">
            Provide the region for this tax rate
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="rate">Rate</label>
          <div class="input-group">
            <input type="number" min="0" max="100" step="0.01" name="rate" id="rate" required class="form-control">
            <span class="input-group-text rounded-end">%</span>
            <div class="invalid-feedback">
              Provide a valid tax rate
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="rate-in-ex">Prices inclusive or exclusive of tax</label>
          <select class="form-select" name="rate-in-ex" id="rate-in-ex" required>
            <option value="inclusive" selected>Inclusive</option>
            <option value="exclusive">Exclusive</option>
          </select>
          <div class="invalid-feedback">
            Select inclusive/exclusive
          </div>
        </div>


        <p>
          <button type="submit" class="btn btn-primary">
            Add rate
          </button>
        </p>
      </form>

    </div>
  </div>

</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->addJs('js/NeedsValidation.js');
$footer->render();
