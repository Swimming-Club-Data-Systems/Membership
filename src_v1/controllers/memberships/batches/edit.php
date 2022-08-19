<?php

$user = app()->user;
$db = app()->db;

$getBatch = $db->prepare("SELECT membershipBatch.ID id, membershipBatch.Completed completed, DueDate due, Total total, PaymentTypes payMethods, PaymentDetails payDetails, membershipBatch.User `user` FROM membershipBatch INNER JOIN users ON users.UserID = membershipBatch.User WHERE membershipBatch.ID = ? AND users.Tenant = ?");
$getBatch->execute([
  $id,
  app()->tenant->getId(),
]);

$batch = $getBatch->fetch(PDO::FETCH_OBJ);

if (!$batch) halt(404);

if (!$user->hasPermission('Admin')) halt(404);

// Get batch items
$getBatchItems = $db->prepare("SELECT membershipBatchItems.ID id, membershipBatchItems.Membership membershipId, membershipBatchItems.Amount amount, membershipBatchItems.Notes notes, members.MForename firstName, members.MSurname lastName, members.ASANumber ngbId, clubMembershipClasses.Type membershipType, clubMembershipClasses.Name membershipName, clubMembershipClasses.Description membershipDescription, membershipYear.ID yearId, membershipYear.Name yearName, membershipYear.StartDate yearStart, membershipYear.EndDate yearEnd FROM membershipBatchItems INNER JOIN membershipYear ON membershipBatchItems.Year = membershipYear.ID INNER JOIN members ON members.MemberID = membershipBatchItems.Member INNER JOIN clubMembershipClasses ON clubMembershipClasses.ID = membershipBatchItems.Membership WHERE Batch = ?");
$getBatchItems->execute([
  $id
]);
$item = $getBatchItems->fetch(PDO::FETCH_OBJ);

$payMethods = json_decode($batch->payMethods);

$canPay = true;
$due = new DateTime($batch->due, new DateTimeZone('Europe/London'));
$due->setTime(0, 0, 0, 0);
$now = new DateTime('now', new DateTimeZone('Europe/London'));
$now->setTime(0, 0, 0, 0);
if ($now > $due) $canPay = false;

$batchUser = new User($batch->user);

$markdown = new \ParsedownExtra();
$markdown->setSafeMode(true);

$session = null;
$getSession = $db->prepare("SELECT `id` FROM `onboardingSessions` WHERE `batch` = ?");
$getSession->execute([
  $id,
]);
$sessionId = $getSession->fetchColumn();
if ($sessionId) $session = \SCDS\Onboarding\Session::retrieve($sessionId);

$pagetitle = "Batch " . htmlspecialchars($id) . " - Membership Centre";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <?php if ($session) { ?>
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('onboarding')) ?>">Onboarding</a></li>
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('onboarding/' . $session->id)) ?>"><?= htmlspecialchars($session->getUser()->getName()) ?></a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit Batch</li>
        <?php } else { ?>
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Memberships</a></li>
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("memberships/batches")) ?>">Batches</a></li>
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("memberships/batches/$id")) ?>">Batch</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit</li>
        <?php } ?>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Edit batch for <?= htmlspecialchars($batchUser->getFullName()) ?>
        </h1>
        <p class="lead mb-0">
          <?= htmlspecialchars($id) ?>
        </p>
      </div>
      <?php if ($session) { ?>
        <div class="col-auto ms-auto">
          <a href="<?= htmlspecialchars(autoUrl('onboarding/sessions/a/' . $session->id)) ?>" class="btn btn-success">Back to onboarding</a>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <?php if (!$canPay) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>This membership batch is overdue</strong>
          </p>
          <p class="mb-0">
            This means you can not pay and will need to ask your membership secretary to create a new batch for you.
          </p>
        </div>
      <?php } ?>

      <h2>Batch Details</h2>

      <dl class="row">

        <dt class="col-3">
          Batch ID
        </dt>
        <dd class="col-9">
          <?= htmlspecialchars($batch->id) ?>
        </dd>

        <?php
        // <dt class="col-3">
        //   Period
        // </dt>
        // <dd class="col-9">
        //   <?= htmlspecialchars((new DateTime($batch->yearStart))->format('j F Y'))  htmlspecialchars((new DateTime($batch->yearEnd))->format('j F Y'))
        // </dd>
        ?>

        <dt class="col-3">
          Amount
        </dt>
        <dd class="col-9" id="formatted-total">
          <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($batch->total), 'GBP')) ?>
        </dd>

        <dt class="col-3">
          Due by end
        </dt>
        <dd class="col-9">
          <?= htmlspecialchars($due->format('j F Y')) ?>
        </dd>

        <?php if (!$batch->completed) { ?>
          <dt class="col-3">
            Pay by
          </dt>
          <dd class="col-9">
            <?php if (sizeof($payMethods) > 0) { ?>
              <ul class="mb-0">
                <?php if (in_array('card', $payMethods)) { ?>
                  <li>Credit/debit card</li>
                <?php } ?>
                <?php if (in_array('dd', $payMethods)) { ?>
                  <li>Next Direct Debit payment</li>
                <?php } ?>
              <?php } else { ?>
                No payment methods - speak to club staff
              <?php } ?>
          </dd>
        <?php } ?>
      </dl>

      <h2>Memberships in batch</h2>

      <div id="batch-items-section"></div>


    </div>
    <div class="col">
      <div class="sticky-top pt-3">
        <div class="card card-body mb-3">
          <h2 class="card-title">Additional memberships</h2>

          <p>
            It's easy to add another membership for a member linked to <?= htmlspecialchars($batchUser->getFullName()) ?>'s account.
          </p>

          <div class="d-grid gap-2">
            <button class="btn btn-success" type="button" id="add-membership-button">
              Add a membership
            </button>
          </div>
        </div>

        <div class="card card-body mb-3">
          <h2 class="card-title">Options</h2>

          <form id="options-form">

            <p class="mb-2">Payment Options</p>
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="payment-card" name="payment-card" value="1" <?php if (in_array('card', $payMethods)) { ?>checked<?php } ?>>
                <label class="form-check-label" for="payment-card">
                  Credit/Debit card
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="payment-direct-debit" name="payment-direct-debit" value="1" <?php if (in_array('dd', $payMethods)) { ?>checked<?php } ?>>
                <label class="form-check-label" for="payment-direct-debit">
                  Direct Debit
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="payment-cash" name="payment-cash" value="1" <?php if (in_array('cash', $payMethods)) { ?>checked<?php } ?> disabled>
                <label class="form-check-label" for="payment-cash">
                  Cash (not supported)
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="payment-cheque" name="payment-cheque" value="1" <?php if (in_array('cheque', $payMethods)) { ?>checked<?php } ?> disabled>
                <label class="form-check-label" for="payment-cheque">
                  Cheque (not supported)
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="payment-wire" name="payment-wire" value="1" <?php if (in_array('wire', $payMethods)) { ?>checked<?php } ?> disabled>
                <label class="form-check-label" for="payment-wire">
                  Bank Transfer (not supported)
                </label>
              </div>
            </div>

            <div class="d-grid gap-2">
              <button class="btn btn-success">
                Save
              </button>
            </div>
          </form>
        </div>

        <!-- <div class="card card-body mb-3">
          <h2 class="card-title">Totals</h2>
        </div> -->
      </div>
    </div>
  </div>

</div>

<div id="js-data" data-list-ajax-url="<?= htmlspecialchars(autoUrl('memberships/batches/edit-items')) ?>" data-add-ajax-url="<?= htmlspecialchars(autoUrl('memberships/batches/get-members')) ?>" data-select-ajax-url="<?= htmlspecialchars(autoUrl('memberships/batches/select-membership')) ?>" data-add-item-ajax-url="<?= htmlspecialchars(autoUrl('memberships/batches/add-membership')) ?>" data-batch-id="<?= htmlspecialchars($id) ?>" data-delete-item-ajax-url="<?= htmlspecialchars(autoUrl('memberships/batches/delete')) ?>" data-update-item-ajax-url="<?= htmlspecialchars(autoUrl('memberships/batches/update')) ?>" data-options-update-ajax-url="<?= htmlspecialchars(autoUrl('memberships/batches/options')) ?>"></div>

<!-- Modal for use by JS code -->
<div class="modal fade" id="main-modal" tabindex="-1" role="dialog" aria-labelledby="main-modal-title" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="main-modal-title">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

        </button>
      </div>
      <div class="modal-body" id="main-modal-body">
        ...
      </div>
      <div class="modal-footer" id="main-modal-footer">
        <button type="button" class="btn btn-dark-l btn-outline-light-d" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="modal-confirm-button" class="btn btn-success">Confirm</button>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('js/memberships/edit-batch.js?v=5');
$footer->render();
