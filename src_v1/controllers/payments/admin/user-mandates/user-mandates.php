<?php

require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$db = app()->db;
$tenant = app()->tenant;

$userName = $db->prepare("SELECT Forename, Surname FROM users WHERE UserID = ? AND Tenant = ?");
$userName->execute([
  $user,
  $tenant->getId()
]);
$un = $userName->fetch(PDO::FETCH_ASSOC);

if (!$un) {
  halt(404);
}

$pagetitle = htmlspecialchars(\SCDS\Formatting\Names::format($un['Forename'], $un['Surname']) . "'s Direct Debit Mandate");

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

/*
 * Get the user's preferred mandate (if exists)
 */
$getPreferred = $db->prepare("SELECT MandateID FROM `paymentPreferredMandate` WHERE `UserID` = ?");
$getPreferred->execute([$user]);
$defaultAcc = null;
if ($row = $getPreferred->fetch()) {
  $defaultAcc = $row['MandateID'];
}

/*
 * Get all mandates
 */
$mandateDetails = $db->prepare("SELECT * FROM `paymentMandates` WHERE `UserID` = ? AND `InUse` = ?");
$mandateDetails->execute([$user, true]);

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("users")) ?>">Users</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("users/" . $user)) ?>"><?= htmlspecialchars(mb_substr($un["Forename"], 0, 1, 'utf-8') . mb_substr($un["Surname"], 0, 1, 'utf-8')) ?></a></li>
      <li class="breadcrumb-item active" aria-current="page">Bank</li>
    </ol>
  </nav>

  <h1>Bank Account Options (GoCardless)</h1>
  <p class="lead">
    <?= htmlspecialchars($un['Forename']) ?>'s mandates
  </p>

  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['MandateDeletedTrue']) && $_SESSION['TENANT-' . app()->tenant->getId()]['MandateDeletedTrue']) { ?>
    <div class="alert alert-success">
      <p class="mb-0">
        <strong>Mandate deleted successfully</strong>
      </p>
      <p class="mb-0">Check below to see if the user needs to set up another mandate to pay by direct debit in future.</p>
    </div>
  <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['MandateDeletedTrue']);
  } ?>

  <p>
    You can view the status of a mandate or cancel it from this page.
  </p>
  <p>
    <strong>Beware:</strong> Mandate cancellations are instant and cannot be reversed.
  </p>

  <?php if ($row = $mandateDetails->fetch(PDO::FETCH_ASSOC)) { ?>
    <ul class="list-group">
      <?php do { ?>
        <li class="list-group-item list-group-item-x">
          <div class="row mb-3">
            <div class="col">
              <?= htmlspecialchars($row['Mandate']) ?> (<?= htmlspecialchars($row['AccountHolderName']) ?>)
            </div>
            <div class="col text-end">
              <?php if ($defaultAcc != null > 1 && $defaultAcc == $row['MandateID']) { ?>
                <span class="badge bg-info">Main Account</span>
              <?php } ?>
            </div>
          </div>
          <div class="mb-3">
            <?= htmlspecialchars(getBankName($row['BankName'])) ?> account ending &middot;&middot;&middot;&middot;&middot;&middot;<?= htmlspecialchars($row['AccountNumEnd']) ?>
          </div>
          <div class="row align-items-center">
            <div class="col-12 col-md-4">
              <div class="d-grid gap-2">
                <a target="_blank" download class="btn btn-dark-l btn-outline-light-d" href="<?= htmlspecialchars(autoUrl("payments/mandates/" . $row['Mandate'] . '/print')) ?>" title="View details about this mandate which are also visible to the user">
                  View printable info
                </a>
              </div>
              <div class="mb-3 d-md-none"></div>
            </div>
            <div class="col-12 col-md-4">
              <div class="d-grid gap-2">
                <a target="_blank" class="btn btn-dark-l btn-outline-light-d" href="<?= htmlspecialchars(autoUrl("payments/mandates/" . $row['Mandate'])) ?>" title="View full details about this mandate">
                  View full details
                </a>
              </div>
              <div class="mb-3 d-md-none"></div>
            </div>
            <div class="col-12 col-md-4">
              <div class="d-grid gap-2">
                <a class="btn btn-danger" href="<?= htmlspecialchars(autoUrl("payments/mandates/" . $row['Mandate'] . '/cancel')) ?>" title="Cancel this mandate">
                  Cancel mandate
                </a>
              </div>
            </div>
          </div>
        </li>
      <?php } while ($row = $mandateDetails->fetch(PDO::FETCH_ASSOC)); ?>
    </ul>
  <?php } else { ?>
    <div class="alert alert-warning">
      <strong><?= htmlspecialchars($un['Forename']) ?> does not have a direct debit set up</strong> <br>
      Ask them to set one up in their club account if they need to pay by Direct Debit.
    </div>
  <?php } ?>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
