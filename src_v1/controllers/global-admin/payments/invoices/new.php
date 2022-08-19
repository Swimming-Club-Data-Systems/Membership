<?php

$db = app()->db;
$getTenants = $db->query("SELECT `Name`, `ID` FROM `tenants` WHERE `Verified` ORDER BY `Name` ASC");

$getProducts = $db->query("SELECT `ID`, `Name`, `Description`, `Updated` FROM `tenantPaymentProducts` ORDER BY `Name` ASC;");

$today = new DateTime('now', new DateTimeZone('Europe/London'));
$firstNextMonth = new DateTime('first day of next month', new DateTimeZone('Europe/London'));

$pagetitle = "New Invoice - Payments - Admin Dashboard - SCDS";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments')) ?>">Pay</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments/invoices')) ?>">Invoices</a></li>
      <li class="breadcrumb-item active" aria-current="page">New</li>
    </ol>
  </nav>

  <h1>
    New Invoice
  </h1>
  <p class="lead">Create a custom invoice.</p>

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['SubscriptionAddError'])) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>There was a problem trying to create the new subscription</strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($_SESSION['SubscriptionAddError']) ?>
          </p>
        </div>
      <?php unset($_SESSION['SubscriptionAddError']);
      } ?>

      <form method="post" class="needs-validation" novalidate>

        <?= \SCDS\CSRF::write() ?>

        <div class="mb-3">
          <label class="form-label" for="subscription-tenant">Tenant</label>
          <select name="subscription-tenant" id="subscription-tenant" class="form-select" required data-payment-methods-ajax-url="<?= htmlspecialchars(autoUrl('admin/payments/subscriptions/new/get-tenant-payment-methods')) ?>">
            <option value="" selected disabled>Select a customer</option>
            <?php while ($tenant = $getTenants->fetch(PDO::FETCH_ASSOC)) { ?>
              <option value="<?= htmlspecialchars($tenant['ID']) ?>"><?= htmlspecialchars($tenant['Name']) ?></option>
            <?php } ?>
          </select>
          <div class="invalid-feedback">
            Select a tenant
          </div>
        </div>

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

        <input type="hidden" name="subscription-plans-object" id="subscription-plans-object" value="">

        <div class="mb-3" id="pays-auto-radios">
          <p class="mb-2">Bills when?</p>
          <div class="form-check">
            <input type="radio" id="bills-when-immediately" name="bills-when" class="form-check-input" value="immediately" required checked>
            <label class="form-check-label" for="bills-when-immediately">Bill selected payment method immediately</label>
          </div>
          <div class="form-check">
            <input type="radio" id="bills-when-manually" name="bills-when" class="form-check-input" value="chooses">
            <label class="form-check-label" for="bills-when-manually">Customer makes manual payment</label>
          </div>
        </div>

        <div class="mb-3" id="payment-method-box">
          <label class="form-label" for="subscription-payment-method">Payment method</label>
          <select name="subscription-payment-method" id="subscription-payment-method" class="form-select" required disabled>
            <option value="" selected disabled>Select a payment method</option>
          </select>
          <div class="invalid-feedback">
            Select a payment method to use
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="subscription-invoice-memo">Invoice memo</label>
          <textarea class="form-control" name="subscription-invoice-memo" id="subscription-invoice-memo" rows="4"></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label" for="subscription-invoice-footer">Invoice footer</label>
          <textarea class="form-control" name="subscription-invoice-footer" id="subscription-invoice-footer" rows="4"></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label" for="invoice-date">Invoice date</label>
          <input type="date" name="invoice-date" id="invoice-date" class="form-control" min="<?= htmlspecialchars($today->format('Y-m-d')) ?>" value="<?= htmlspecialchars($today->format('Y-m-d')) ?>">
          <div class="invalid-feedback">
            Please provide a valid date to start billing this subscription on
          </div>
        </div>

        <p>
          <button type="submit" class="btn btn-primary">
            Create invoice
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
$footer->addJS("js/global-admin/payments/invoices/new.js", true);
$footer->addJs('js/NeedsValidation.js');
$footer->render();
