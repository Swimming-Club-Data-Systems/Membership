<?php

$db = app()->db;

$getProducts = $db->prepare("SELECT `ID`, `Name`, `Description`, `Updated` FROM `tenantPaymentProducts` WHERE `ID` = ?;");
$getProducts->execute([
  $id,
]);
$product = $getProducts->fetch(PDO::FETCH_ASSOC);

if (!$product) halt(404);

$getPlans = $db->prepare("SELECT `ID`, `PricePerUnit`, `UsageType`, `Currency`, `BillingInterval`, `Name`, `Updated` FROM `tenantPaymentPlans` WHERE `Product` = ? ORDER BY `PricePerUnit` ASC, `Name` ASC");
$getPlans->execute([
  $id,
]);
$plan = $getPlans->fetch(PDO::FETCH_ASSOC);

$markdown = new ParsedownExtra();
$markdown->setSafeMode(true);

$pagetitle = htmlspecialchars($product['Name']) . " - Products - Payments - Admin Dashboard - SCDS";

$formatter = new NumberFormatter(app()->locale, NumberFormatter::CURRENCY);

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments')) ?>">Pay</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments/products')) ?>">Products</a></li>
      <li class="breadcrumb-item active" aria-current="page">Product</li>
    </ol>
  </nav>

  <h1>
    <?= htmlspecialchars($product['Name']) ?>
  </h1>
  <p class="lead">Product</p>

  <?php if (isset($_SESSION['ProductAddSuccess'])) { ?>
    <div class="alert alert-success">
      <p class="mb-0">
        <strong>New product added successfully</strong>
      </p>
    </div>
  <?php unset($_SESSION['ProductAddSuccess']);
  } ?>

  <?php if ($product['Description']) { ?>
    <h2>Description</h2>
    <div class="row">
      <div class="col-lg-8">
        <?= $markdown->text($product['Description']) ?>
      </div>
    </div>
  <?php } ?>

  <h2>Plans</h2>
  <?php if ($plan) { ?>
    <div class="list-group mb-3">
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
        <div class="list-group-item">
          <p class="mb-0">
            <strong><?= htmlspecialchars($plan['Name']) ?></strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($formatter->formatCurrency((string) (\Brick\Math\BigDecimal::of((string) $plan['PricePerUnit']))->withPointMovedLeft(2)->toScale(2), $plan['Currency'])) ?><?php if ($plan['UsageType'] == 'recurring') { ?>, Recurs every <?= htmlspecialchars($intervalString) ?><?php } ?>
          </p>
        </div>
      <?php } while ($plan = $getPlans->fetch(PDO::FETCH_ASSOC)); ?>
    </div>
  <?php } else { ?>
    <div class="alert alert-warning">
      <p class="mb-0">
        <strong>There are no payment plans available for this product</strong>
      </p>
      <p class="mb-0">
        You must add a payment plan to be able to create subscriptions with this product.
      </p>
    </div>
  <?php } ?>

  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
    Add plan
  </button>

</div>

<!-- Modal -->
<div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">Add plan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

        </button>
      </div>
      <div class="modal-body">
        <form id="new-plan-form" class="needs-validation" novalidate data-ajax-url="<?= htmlspecialchars(autoUrl('admin/payments/plans/new')) ?>">

          <input type="hidden" name="product" id="plan-product" value="<?= htmlspecialchars($id) ?>" required>

          <?= \SCDS\CSRF::write() ?>

          <div class="mb-3">
            <label class="form-label" for="plan-name">Name</label>
            <input type="text" name="plan-name" id="plan-name" class="form-control" required>
            <div class="invalid-feedback">You must supply a name for this plan</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="plan-price">Price per unit</label>
            <input type="number" min="0" step="0.01" name="plan-price" id="plan-price" class="form-control" required>
            <div class="invalid-feedback">You must supply a price per unit for this plan</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="plan-currency">Currency</label>
            <select class="form-select" required name="plan-currency" id="plan-currency">
              <option value="gbp" selected>British Pounds (GBP)</option>
            </select>
            <div class="invalid-feedback">You must supply a currency for this plan</div>
          </div>

          <div class="mb-3">
            <p class="mb-2">
              Billing interval (frequency)
            </p>
            <div class="input-group">
              <span class="input-group-text">Every</span>
              <input type="number" min="1" step="1" value="1" aria-label="Billing Frequency" class="form-control" name="plan-frequency-n" id="plan-frequency-n" required>
              <select class="form-select" aria-label="Billing Frequency Type" required name="plan-frequency-type" id="plan-frequency-type">
                <option value="days">Days</option>
                <option value="weeks">Weeks</option>
                <option value="months" selected>Months</option>
                <option value="years">Years</option>
              </select>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" form="new-plan-form" class="btn btn-primary">Add plan</button>
      </div>
    </div>
  </div>
</div>

<script>
  let form = document.getElementById('new-plan-form');
  form.addEventListener('submit', async ev => {
    ev.preventDefault();

    if (form.checkValidity()) {
      const formData = new FormData(form);

      try {
        // POST the FormData object
        const response = await fetch(form.dataset.ajaxUrl, {
          method: 'POST',
          redirect: 'error',
          body: formData // body data type must match "Content-Type" header
        });

        let body = response.body;
        console.log(body);

        let json = await response.json();

        console.log(response);

        if (json.success) {
          // Yay
          alert('Successful');
        } else {
          alert('Error\r\n' + json.error);
        }

      } catch (err) {
        console.warn(err);
      }
    }

  })
</script>

<?php

$footer = new \SCDS\RootFooter();
$footer->addJs('js/NeedsValidation.js');
$footer->render();
