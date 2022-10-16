<?php

$user = app()->user;
$db = app()->db;

$getBatch = $db->prepare("SELECT membershipBatch.ID id, membershipBatch.Completed completed, DueDate due, Total total, PaymentTypes payMethods, PaymentDetails payDetails, users.UserID user, users.Forename firstName, users.Surname lastName FROM membershipBatch INNER JOIN users ON users.UserID = membershipBatch.User WHERE membershipBatch.ID = ? AND users.Tenant = ?");
$getBatch->execute([
  $id,
  app()->tenant->getId(),
]);

$batch = $getBatch->fetch(PDO::FETCH_OBJ);

if (!$batch) halt(404);

if ($batch->user != $user->getId() && !$user->hasPermission('Admin')) halt(404);

// Get batch items
$getBatchItems = $db->prepare("SELECT membershipBatchItems.ID id, membershipBatchItems.Membership membershipId, membershipBatchItems.Amount amount, membershipBatchItems.Notes notes, members.MForename firstName, members.MSurname lastName, members.ASANumber ngbId, clubMembershipClasses.Type membershipType, clubMembershipClasses.Name membershipName, clubMembershipClasses.Description membershipDescription, membershipYear.ID yearId, membershipYear.Name yearName, membershipYear.StartDate yearStart, membershipYear.EndDate yearEnd FROM membershipBatchItems INNER JOIN membershipYear ON membershipBatchItems.Year = membershipYear.ID INNER JOIN members ON members.MemberID = membershipBatchItems.Member INNER JOIN clubMembershipClasses ON clubMembershipClasses.ID = membershipBatchItems.Membership WHERE Batch = ?");
$getBatchItems->execute([
  $id
]);
$item = $getBatchItems->fetch(PDO::FETCH_OBJ);

$payMethods = json_decode($batch->payMethods);

$payMethodStrings = [
  'card' => 'credit/debit card',
  'dd' => 'next Direct Debit payment',
  'cash' => 'cash in person',
  'cheque' => 'cheque',
  'bacs' => 'bank transfer',
];

$canPay = true;
$due = new DateTime($batch->due, new DateTimeZone('Europe/London'));
$due->setTime(0, 0, 0, 0);
$now = new DateTime('now', new DateTimeZone('Europe/London'));
$now->setTime(0, 0, 0, 0);
$canPay = $now < $due;

$markdown = new \ParsedownExtra();
$markdown->setSafeMode(true);

$pagetitle = "Batch " . htmlspecialchars($id) . " - Membership Centre";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Memberships</a></li>
        <li class="breadcrumb-item active" aria-current="page">Batch</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Batch for <?= htmlspecialchars($batch->firstName . ' ' . $batch->lastName) ?>
        </h1>
        <p class="lead mb-0">
          <?= htmlspecialchars($id) ?>
        </p>
      </div>
      <?php if ($user->hasPermission('Admin')) { ?>
        <div class="col-auto ms-auto">
          <a href="<?= htmlspecialchars(autoUrl("memberships/batches/$id/edit")) ?>" class="btn btn-success">Edit</a>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['SentEmail']) && $_SESSION['SentEmail']) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>Email sent successfully</strong>
          </p>
        </div>
      <?php unset($_SESSION['SentEmail']);
      } ?>

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

        <dt class="col-3">
          Amount
        </dt>
        <dd class="col-9">
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
                <?php foreach ($payMethods as $method) { ?>
                  <li><?= htmlspecialchars(mb_strtoupper(mb_substr($payMethodStrings[$method], 0, 1)) . mb_substr($payMethodStrings[$method], 1)) ?></li>
                <?php } ?>
              <?php } else { ?>
                No payment methods - speak to club staff
              <?php } ?>
          </dd>
        <?php } ?>
      </dl>

      <?php if (!$batch->completed && $batch->total > 0 && sizeof($payMethods) > 0 && $canPay && $batch->user == $user->getId()) { ?>
        <form method="post" class="needs-validation" novalidate>
          <h2>Pay</h2>
          <?php if (sizeof($payMethods) > 1) { ?>
            <!-- Select payment method -->
            <p class="mb-2">Choose a payment method</p>

            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="pay-method" id="pay-card" value="card" <?php if (!in_array('card', $payMethods)) { ?>disabled<?php } ?> required>
                <label class="form-check-label" for="pay-card">
                  Credit/debit card
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="pay-method" id="pay-dd" value="dd" <?php if (!in_array('dd', $payMethods)) { ?>disabled<?php } ?>>
                <label class="form-check-label" for="pay-dd">
                  Next Direct Debit payment
                </label>
              </div>

              <div class="form-check">
                <input class="form-check-input" type="radio" name="pay-method" id="pay-cash" value="cash" <?php if (!in_array('cash', $payMethods)) { ?>disabled<?php } ?>>
                <label class="form-check-label" for="pay-cash">
                  Cash
                </label>
              </div>

              <div class="form-check">
                <input class="form-check-input" type="radio" name="pay-method" id="pay-cheque" value="cheque" <?php if (!in_array('cheque', $payMethods)) { ?>disabled<?php } ?>>
                <label class="form-check-label" for="pay-cheque">
                  Cheque
                </label>
              </div>

              <div class="form-check mb-0">
                <input class="form-check-input" type="radio" name="pay-method" id="pay-bacs" value="bacs" <?php if (!in_array('bacs', $payMethods)) { ?>disabled<?php } ?>>
                <label class="form-check-label" for="pay-bacs">
                  Bank transfer
                </label>
              </div>
            </div>
          <?php } else if (sizeof($payMethods) > 0) { ?>
            <!-- Just go straight to payment -->
            <input type="hidden" name="pay-method" value="<?= htmlspecialchars($payMethods[0]) ?>">
            <p>You can only pay for this batch with <?= htmlspecialchars($payMethodStrings[$payMethods[0]]) ?>.</p>
          <?php } ?>
          <p class="d-grid mb-1">
            <button type="submit" class="btn btn-success">Pay for memberships <i class="fa fa-chevron-right" aria-hidden="true"></i></button>
          </p>
          <p class="small text-muted">With SCDS Checkout</p>
        </form>
      <?php } ?>

      <h2>Memberships</h2>

      <?php if ($item) { ?>
        <ul class="list-group mb-3">
          <?php do { ?>
            <li class="list-group-item">
              <h3><?= htmlspecialchars($item->firstName . ' ' . $item->lastName) ?></h3>
              <p class="lead"><?= htmlspecialchars($item->membershipName) ?></p>

              <dl class="row">
                <dt class="col-3">
                  Membership ID
                </dt>
                <dd class="col-9">
                  <?= htmlspecialchars($item->membershipId) ?>
                </dd>

                <dt class="col-3">
                  Period
                </dt>
                <dd class="col-9">
                  <?= htmlspecialchars($item->yearName) ?> (<?= htmlspecialchars((new DateTime($item->yearStart))->format('j F Y')) ?> to <?= htmlspecialchars((new DateTime($item->yearEnd))->format('j F Y')) ?>)
                </dd>

                <dt class="col-3">
                  Amount
                </dt>
                <dd class="col-9">
                  <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($item->amount), 'GBP')) ?>
                </dd>

                <dt class="col-3">
                  <?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> #
                </dt>
                <dd class="col-9">
                  <?= htmlspecialchars($item->ngbId) ?>
                </dd>

                <?php if ($item->notes) { ?>
                  <dt class="col-3">
                    Notes
                  </dt>
                  <dd class="col-9 mb-n2">
                    <?= $markdown->text($item->notes) ?>
                  </dd>
                <?php } ?>

                <?php if ($item->membershipDescription) { ?>
                  <dt class="col-3">
                    Membership Description
                  </dt>
                  <dd class="col-9 mb-n2">
                    <?= $markdown->text($item->membershipDescription) ?>
                  </dd>
                <?php } ?>
              </dl>
            </li>
          <?php } while ($item = $getBatchItems->fetch(PDO::FETCH_OBJ)); ?>
        </ul>

        <?php if (app()->user->hasPermission('Admin')) { ?>
          <p>
            <a href="<?= htmlspecialchars(autoUrl("memberships/batches/$id/send-email")) ?>">Send email to user</a>
          </p>
        <?php } ?>
      <?php } else { ?>
        <div class="alert alert-warning">
          There are no memberships in this batch
        </div>
      <?php } ?>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('js/NeedsValidation.js');
$footer->render();
