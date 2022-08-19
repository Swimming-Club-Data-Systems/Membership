<?php

// require 'GoCardlessSetup.php';

$db = app()->db;

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$use_white_background = true;
$pagetitle = "Direct Debit Mandate";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

/*
 * Get the user's preferred mandate (if exists)
 */
$getPreferred = $db->prepare("SELECT MandateID FROM `paymentPreferredMandate` WHERE `UserID` = ?");
$getPreferred->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
$defaultAcc = null;
if ($row = $getPreferred->fetch()) {
  $defaultAcc = $row['MandateID'];
}

/*
 * Get all mandates
 */
$mandateDetails = $db->prepare("SELECT * FROM `paymentMandates` WHERE `UserID` = ? AND `InUse` = ?");
$mandateDetails->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], true]);

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("payments")?>">Payments</a></li>
      <li class="breadcrumb-item active" aria-current="page">Bank</li>
    </ol>
  </nav>

	<h1>Bank Account Options</h1>
  <p class="lead">
    Your Direct Debit Mandate is an agreement with your bank allowing us to take payments.
  </p>
  <p>
    To canel a mandate, contact your bank in person, by phone or through online banking, where you can cancel a mandate yourself.
  </p>
  <?php if ($row = $mandateDetails->fetch(PDO::FETCH_ASSOC)) { ?>
  <div class="table-responsive-md bg-white">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Mandate ID</th>
          <th>Bank Name</th>
          <th>Account Holder</th>
          <th>Account Number</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php do { ?>
        <tr>
          <td class="font-monospace">
            <a target="_blank" href="<?=autoUrl("payments/mandates/" . $row['Mandate'] . '/print')?>" title="View Mandate Details">
              <?=htmlspecialchars($row['Mandate'])?>
            </a>
          </td>
          <td>
            <?=htmlspecialchars(getBankName($row['BankName']))?>
          </td>
          <td class="font-monospace" title="Name on bank account">
            <?=htmlspecialchars($row['AccountHolderName'])?>
          </td>
          <td class="font-monospace" title="Last two digits of your account number">
            ******<?=htmlspecialchars($row['AccountNumEnd'])?>
          </td>
          <?php if ($defaultAcc != null > 1 && $defaultAcc != $row['MandateID']) { ?>
          <td>
            <a href="<?=htmlspecialchars(autoUrl("payments/mandates/" . $row['MandateID'] . '/set-default'))?>">
              Make Default
            </a>
          </td>
          <?php } else { ?>
          <td>
            <small>Default Mandate</small>
          </td>
          <?php } ?>
        </tr>
      <?php } while ($row = $mandateDetails->fetch(PDO::FETCH_ASSOC)); ?>
      </tbody>
    </table>
  </div>
  <p class="mb-0">
    <a href="<?=autoUrl("payments/setup")?>" class="btn btn-success">
      Setup New Direct Debit
    </a>
  </p>
	<?php } else { ?>
	<div class="alert alert-warning">
		<strong>You do not have a direct debit set up</strong> <br>
		<a class="alert-link" href="<?=autoUrl("payments/setup")?>">Set one up now</a>
	</div>
	<?php } ?>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
