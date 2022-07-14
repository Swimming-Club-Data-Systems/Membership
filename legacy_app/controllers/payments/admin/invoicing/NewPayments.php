<?php

$pagetitle = "New Payment";

$db = app()->db;
$tenant = app()->tenant;

$categories = $db->prepare("SELECT `UniqueID`, `Name` FROM paymentCategories WHERE `Tenant` = ? AND `Show` ORDER BY `Name` ASC");
$categories->execute([
  $tenant->getId(),
]);
$category = $categories->fetch(PDO::FETCH_ASSOC);

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('payments')) ?>">Payments</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('payments/invoice-payments')) ?>">Invoicing</a></li>
      <li class="breadcrumb-item active" aria-current="page">New</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>New invoice payment</h1>
      <p class="lead">Add a new payment or credit to an account</p>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NewPaymentSuccessMessage'])) { ?>
        <div class="alert alert-success">
          <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['NewPaymentSuccessMessage']) ?>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['NewPaymentSuccessMessage']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NewPaymentErrorMessage'])) { ?>
        <div class="alert alert-danger">
          <p class="mb-0"><strong>An error occured and we have not added the payment</strong></p>
          <p class="mb-0"><?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['NewPaymentErrorMessage']) ?></p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['NewPaymentErrorMessage']);
      } ?>

      <form id="payment-form" method="post" class="needs-validation" novalidate>
        <div class="mb-3">
          <label class="form-label" for="user-first-name">User's name</label>
          <input type="text" class="form-control" id="user-first-name" name="user-first-name" aria-describedby="user-first-name-help" data-ajax-url="<?= htmlspecialchars(autoUrl("payments/invoice-payments/new/search-users")) ?>">
          <small id="user-first-name-help" class="form-text text-muted">Search for a user by name.</small>
        </div>

        <div class="mb-3">
          <label class="form-label" for="user-select">Select user</label>
          <select class="form-select overflow-hidden" id="user-select" name="user-select" disabled data-ajax-url="<?= htmlspecialchars(autoUrl("payments/invoice-payments/new/get-user")) ?>">
            <option value="none" selected>Search for a user by name</option>
          </select>
          <small id="user-select-help" class="form-text text-muted">Pick a user from this drop down.</small>
        </div>

        <div id="user-info-box"></div>

        <input type="hidden" name="user-id" id="user-id">

        <div id="payment-details" class="d-none">

          <div class="mb-3">
            <label class="form-label" for="description">Payment description</label>
            <input required mmaxlength="500" type="text" class="form-control" name="description" id="description" aria-describedby="description-help">
            <div class="invalid-feedback">
              Invalid description
            </div>
            <small id="description-help" class="form-text text-muted">The description for this payment shown on the user's billing statement. Make it descriptive enough that they will be able to identify the reason for this item.</small>
          </div>

          <div class="mb-3">
            <label class="form-label" for="amount">
              Amount to <span id="amount-type">charge</span>
            </label>
            <div class="input-group">
              <div class="input-group-text font-monospace">&pound;</div>
              <input required type="number" pattern="[0-9]*([\.,][0-9]*)?" class="form-control font-monospace" id="amount" name="amount" placeholder="0.00" min="0" max="1000" step="0.01">
              <div class="invalid-feedback">
                You must enter an amount between &pound;0 and £1000.
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="payment-category">
              Category
            </label>
            <select class="form-select" id="payment-category" name="payment-category" <?php if (!$category) { ?>disabled<?php } ?>>
              <?php if (!$category) { ?>
                <option value="none" selected>No categories available</option>
              <?php } else { ?>
                <option value="none" selected>Uncategorised</option>
                <?php do { ?>
                  <option value="<?= htmlspecialchars($category['UniqueID']) ?>"><?= htmlspecialchars($category['Name']) ?></option>
                <?php } while ($category = $categories->fetch(PDO::FETCH_ASSOC)); ?>
              <?php } ?>
            </select>
          </div>

          <div class="mb-3" id="radios">
            <label>Type</label>
            <div class="form-check">
              <input type="radio" id="type-payment" name="type" class="form-check-input" checked required value="Payment">
              <label class="form-check-label" for="type-payment">Payment</label>
            </div>
            <div class="form-check">
              <input type="radio" id="type-credit" name="type" class="form-check-input" required value="Credit">
              <label class="form-check-label" for="type-credit">Credit (refund)</label>
            </div>
          </div>

          <?= SCDS\CSRF::write() ?>
          <?= SCDS\FormIdempotency::write() ?>

          <p>Once you add a charge to an account, you won't be able to cancel it. If you make a mistake or need to reverse it, you would need to add another charge or a credit (refund).</p>

          <p>
            <button type="submit" class="btn btn-primary" id="payment-submit-button" disabled>Add charge</button>
          </p>

          <div id="error-notice" class="alert alert-warning d-none">
            <p class="mb-0">
              <strong>Please complete the form</strong>
            </p>
            <p class="mb-0">
              A description, amount and type (charge or credit) is required.
            </p>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="confirmation-modal" tabindex="-1" role="dialog" aria-labelledby="confirmation-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmation-modal-label">Confirmation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
        <p>Please confirm that you want to make a <span id="confirmation-modal-pay-credit"></span> of &pound;<span id="confirmation-modal-amount"></span> to <span id="confirmation-modal-name"></span>'s account. Additional account details are shown below.</p>
        <div class="cell">
          <h2 id="confirmation-modal-user-info-name"></h2>
          <dl class="row mb-0">
            <dt class="col-sm-3">Email</dt>
            <dd class="col-sm-9 text-truncate"><a href="" id="confirmation-modal-user-info-email"></a></dd>

            <dt class="col-sm-3">Phone</dt>
            <dd class="col-sm-9"><a href="" id="confirmation-modal-user-info-phone"></a></dd>

            <dt class="col-sm-3">Address</dt>
            <dd class="col-sm-9 mb-0"><span id="confirmation-modal-user-info-address"></span></dd>
        </div>
        <p class="mb-0">A confirmation email detailing this statement item will be sent to the user.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirm" class="btn btn-primary">Confirm</button>
      </div>
    </div>
  </div>
</div>



<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/numerical/bignumber.min.js");
$footer->addJS("js/payments/NewInvoice.js?v=1");
$footer->addJS("js/NeedsValidation.js");
$footer->render();
