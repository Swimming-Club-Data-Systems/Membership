<?php

$db = app()->db;
$tenant = app()->tenant;

$disabled = "";

$numFormat = new NumberFormatter("en", NumberFormatter::SPELLOUT);

$sql = null;

$parentName = null;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
  $sql = $db->prepare("SELECT * FROM ((((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) LEFT JOIN stripePayments ON galaEntries.StripePayment = stripePayments.ID) LEFT JOIN stripePayMethods ON stripePayMethods.ID = stripePayments.Method) WHERE galas.Tenant = ? AND `EntryID` = ? AND members.UserID = ?;");
  $sql->execute([
    $tenant->getId(),
    $id,
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
  ]);
} else {
  $sql = $db->prepare("SELECT * FROM ((((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) LEFT JOIN stripePayments ON galaEntries.StripePayment = stripePayments.ID) LEFT JOIN stripePayMethods ON stripePayMethods.ID = stripePayments.Method) WHERE galas.Tenant = ? AND `EntryID` = ?");
  $sql->execute([
    $tenant->getId(),
    $id
  ]);

  $getParentName = $db->prepare("SELECT Forename, Surname FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN users ON members.UserID = users.UserID) WHERE members.Tenant = ? AND galaEntries.EntryID = ?");
  $getParentName->execute([
    $tenant->getId(),
    $id
  ]);
  if ($parent = $getParentName->fetch(PDO::FETCH_ASSOC)) {
    $parentName = \SCDS\Formatting\Names::format($parent['Forename'], $parent['Surname']);
  } else {
    $parentName = 'Unknown User';
  }
}
$row = $sql->fetch(PDO::FETCH_ASSOC);

if ($row == null) {
  halt(404);
}

$galaData = new GalaPrices($db, $row["GalaID"]);

$closingDate = new DateTime($row['ClosingDate'], new DateTimeZone('Europe/London'));
$theDate = new DateTime('now', new DateTimeZone('Europe/London'));

$locked = false;
if (bool($row['Charged']) || bool($row['EntryProcessed']) || ($closingDate < $theDate && ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Admin' && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Galas')) || (bool($row['Locked']) && ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Admin' && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Galas'))) {
  $locked = true;
}

$closingDate = $closingDate->format('Y-m-d');
$theDate = $theDate->format('Y-m-d');

$swimsArray = ['25Free', '50Free', '100Free', '200Free', '400Free', '800Free', '1500Free', '25Back', '50Back', '100Back', '200Back', '25Breast', '50Breast', '100Breast', '200Breast', '25Fly', '50Fly', '100Fly', '200Fly', '100IM', '150IM', '200IM', '400IM',];
$swimsTextArray = ['25&nbsp;Free', '50&nbsp;Free', '100&nbsp;Free', '200&nbsp;Free', '400&nbsp;Free', '800&nbsp;Free', '1500&nbsp;Free', '25&nbsp;Back', '50&nbsp;Back', '100&nbsp;Back', '200&nbsp;Back', '25&nbsp;Breast', '50&nbsp;Breast', '100&nbsp;Breast', '200&nbsp;Breast', '25&nbsp;Fly', '50&nbsp;Fly', '100&nbsp;Fly', '200&nbsp;Fly', '100&nbsp;IM', '150&nbsp;IM', '200&nbsp;IM', '400&nbsp;IM',];
$swimsTimeArray = ['25FreeTime', '50FreeTime', '100FreeTime', '200FreeTime', '400FreeTime', '800FreeTime', '1500FreeTime', '25BackTime', '50BackTime', '100BackTime', '200BackTime', '25BreastTime', '50BreastTime', '100BreastTime', '200BreastTime', '25FlyTime', '50FlyTime', '100FlyTime', '200FlyTime', '100IMTime', '150IMTime', '200IMTime', '400IMTime',];
$rowArray = [1, null, null, null, null, null, 2, 1,  null, null, 2, 1, null, null, 2, 1, null, null, 2, 1, null, null, 2];
$rowArrayText = ["Freestyle", null, null, null, null, null, 2, "Backstroke",  null, null, 2, "Breaststroke", null, null, 2, "Butterfly", null, null, 2, "Individual Medley", null, null, 2];

$disabled = "";

$pagetitle = htmlspecialchars(\SCDS\Formatting\Names::format($row['MForename'], $row['MSurname']) . " - " . $row['GalaName'] . "");
include BASE_PATH . "views/header.php";
include "galaMenu.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas") ?>">Galas</a></li>
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas/entries") ?>">My entries</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars(mb_substr($row["MForename"], 0, 1, 'utf-8') . mb_substr($row["MSurname"], 0, 1, 'utf-8')) ?> (<?= htmlspecialchars($row['GalaName']) ?>)</li>
      </ol>
    </nav>
    <div>
      <h1><?= htmlspecialchars($row['MForename'] . " " . $row['MSurname'][0]) ?>'s entry for <?= htmlspecialchars($row['GalaName']) ?></h1>
      <p class="lead mb-0">Closing date: <?= date('j F Y', strtotime($row['ClosingDate'])) ?></p>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="">
    <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['UpdateError']) { ?>
      <div class="alert alert-danger">A database error occured which prevented us saving the changes.</div>
    <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateError']);
    } ?>

    <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['UpdateSuccess']) { ?>
      <div class="alert alert-success">All changes to your gala entry have been saved.</div>
    <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateSuccess']);
    } ?>

    <?php if (bool($row['HyTek'])) { ?>
      <h2>Provide times</h2>
      <p class="lead">
        Times must be provided manually at this gala.
      </p>

      <p>
        We're sorry about the inconvenience this causes.
      </p>

      <p>
        <a href="<?= autoUrl("galas/entries/" . $row['EntryID'] . "/manual-time") ?>" class="btn btn-primary">
          Provide times
        </a>
      </p>
    <?php } ?>

    <?php if ($locked) { ?>
      <div class="alert alert-warning">
        <strong>We've already processed this gala entry, our closing date has passed or you have already paid</strong> <br>If there are any changes you need to make, please contact the Gala Administrator directly.
      </div>
    <?php $disabled .= " onclick=\"return false;\" disabled ";
    } ?>
    <h2>Select Swims</h2>
    <p class="lead">Select the events to enter at this gala</p>
    <form method="post">

      <div id="gala-checkboxes">
        <?php for ($i = 0; $i < sizeof($swimsArray); $i++) {
          if ($rowArray[$i] == 1) { ?>
            <div class="row mb-3">
              <?php }
            if ($galaData->getEvent($swimsArray[$i])->isEnabled()) {
              if ($row[$swimsArray[$i]] == 1) { ?>
                <div class="col-sm-4 col-md-2">
                  <div class="form-check">
                    <input type="checkbox" value="1" class="form-check-input" id="<?php echo $swimsArray[$i]; ?>" checked <?php echo $disabled; ?> name="<?php echo $swimsArray[$i]; ?>" data-event-fee="<?= htmlspecialchars($galaData->getEvent($swimsArray[$i])->getPrice()) ?>">
                    <label class="form-check-label" for="<?php echo $swimsArray[$i]; ?>"><?php echo $swimsTextArray[$i]; ?></label>
                  </div>
                </div>
              <?php } else { ?>
                <div class="col-sm-4 col-md-2">
                  <div class="form-check">
                    <input type="checkbox" value="1" class="form-check-input" id="<?php echo $swimsArray[$i]; ?>" <?php echo $disabled; ?> name="<?php echo $swimsArray[$i]; ?>" data-event-fee="<?= htmlspecialchars($galaData->getEvent($swimsArray[$i])->getPrice()) ?>">
                    <label class="form-check-label" for="<?php echo $swimsArray[$i]; ?>"><?php echo $swimsTextArray[$i]; ?></label>
                  </div>
                </div>
              <?php }
            }
            if ($rowArray[$i] == 2) { ?>
            </div>
        <?php }
          } ?>
      </div>

      <?php if (!$locked) { ?>
        <p>
          Your entry fee is <strong>&pound;<span id="total-field" data-total="<?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $row['FeeToPay'])->withPointMovedRight(2)->toInt())) ?>"><?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $row['FeeToPay'])->toScale(2))) ?></span></strong>.
        </p>

        <?php if ($row['ProcessingFee']) { ?>
        <p>
          Your entry fee includes a processing fee of £<?= htmlspecialchars(MoneyHelpers::intToDecimal($row['ProcessingFee'])) ?>
        </p>
        <?php } ?>

        <input type="hidden" value="<?= htmlspecialchars($row['EntryID']) ?>" name="entryID">
        <p>
          <button type="submit" id="submit" class="btn btn-success">Update</button>
        </p>
      <?php } ?>

      <?php if ($row['StripePayment'] != null && bool($row['Paid'])) {
        $getEntryPaymentCount = $db->prepare("SELECT COUNT(*) FROM galaEntries WHERE galaEntries.StripePayment = ?");
        $getEntryPaymentCount->execute([$row['StripePayment']]);
        $countPaid = $getEntryPaymentCount->fetchColumn(); ?>
        <h2>Payment</h2>
        <p class="lead"><?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') { ?>You<?php } else { ?><?= htmlspecialchars($parentName) ?><?php } ?> paid for this gala entry by card</p>
        <div class="row align-items-center mb-3">
          <div class="col-auto">
            <img alt="" src="<?= autoUrl("img/stripe/" . $row['Brand'] . ".png", false) ?>" srcset="<?= autoUrl("img/stripe/" . $row['Brand'] . "@2x.png", false) ?> 2x, <?= autoUrl("img/stripe/" . $row['Brand'] . "@3x.png", false) ?> 3x" style="width:40px;"> <span class="visually-hidden"><?= htmlspecialchars(getCardBrand($row['Brand'])) ?></span>
          </div>
          <div class="col-auto">
            <p class="my-0">
              &#0149;&#0149;&#0149;&#0149; <?= htmlspecialchars($row['Last4']) ?>
            </p>
          </div>
        </div>
        <?php if ($countPaid > 1) { ?>
          <p>
            <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') { ?>You<?php } else { ?><?= htmlspecialchars($parentName) ?><?php } ?> paid for <?= htmlspecialchars($numFormat->format($countPaid)) ?> gala entries as part of this transaction.
          </p>
        <?php } ?>
        <p>
          <a href="<?= htmlspecialchars(autoUrl("payments/card-transactions/" . $row['StripePayment'])) ?>" class="btn btn-primary">View transaction</a>
        </p>
      <?php } ?>
    </form>
  </div>
</div>

<?php
$footer = new \SCDS\Footer();
if (!$locked) {
  $footer->addJS("js/numerical/bignumber.min.js");
  $footer->addJS("js/gala-entries/EditEntry.js");
}
$footer->render();
