<?php

$userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];

$db = app()->db;
$tenant = app()->tenant;

$entries = $db->prepare("SELECT EntryID, GalaName, ClosingDate, GalaVenue, MForename, MSurname, EntryProcessed Processed, Charged, Refunded, Locked, Vetoable, FeeToPay, RequiresApproval, Approved FROM ((galaEntries INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) INNER JOIN members ON galaEntries.MemberID = members.MemberID) WHERE GalaDate >= CURDATE() AND members.UserID = ?");
$entries->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
$entry = $entries->fetch(PDO::FETCH_ASSOC);

$timesheets = $db->prepare("SELECT DISTINCT `galas`.`GalaID`, `GalaName`, `GalaVenue` FROM ((`galas` INNER JOIN `galaEntries` ON `galas`.`GalaID` = `galaEntries`.`GalaID`) INNER JOIN members ON galaEntries.MemberID = members.MemberID) WHERE `GalaDate` >= CURDATE() AND members.UserID = ? ORDER BY `GalaDate` ASC");
$timesheets->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
$timesheet = $timesheets->fetch(PDO::FETCH_ASSOC);

$now = new DateTime('now', new DateTimeZone('Europe/London'));

$pagetitle = "My Gala Entries";
include BASE_PATH . "views/header.php";
include "galaMenu.php";

?>

<div class="front-page" style="margin-bottom: -1rem;">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas") ?>">Galas</a></li>
        <li class="breadcrumb-item active" aria-current="page">My entries</li>
      </ol>
    </nav>
    <h1>My Gala Entries</h1>
    <p class="lead">Manage your gala entries</p>

    <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['VetoTrue']) && $_SESSION['TENANT-' . app()->tenant->getId()]['VetoTrue']) { ?>
      <div class="alert alert-success">
        <p class="mb-0">
          <strong>Your gala entry has been vetoed</strong>
        </p>
        <p class="mb-0">
          All swims withdrawn.
        </p>
      </div>
    <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['VetoTrue']);
    } ?>

    <?php if ($entry) { ?>
      <h2 class="mb-4">
        Your gala entries
      </h2>

      <div class="news-grid mb-4">
        <?php do {
          $closingDate = new DateTime($entry['ClosingDate']);

        ?>
          <a href="<?= autoUrl("galas/entries/" . $entry['EntryID']) ?>">
            <div>
              <span class="title mb-0 justify-content-between align-items-start">
                <span><?= htmlspecialchars(\SCDS\Formatting\Names::format($entry['MForename'], $entry['MSurname'])) ?></span>
                <span class="text-end">
                  <?php if ($now <= $closingDate && !$entry['Charged'] && !$entry['Processed'] && !$entry['Locked']) { ?><span class="ms-2 badge bg-success">EDITABLE</span><?php } ?>
                  <?php if ($entry['Charged']) { ?><span class="ms-2 badge bg-warning"><i class="fa fa-money" aria-hidden="true"></i> PAID</span><?php } ?>
                  <?php if ($entry['Vetoable']) { ?><span class="ms-2 badge bg-info">VETOABLE</span><?php } ?>
                  <?php if ($entry['RequiresApproval'] && $entry['Approved']) { ?><abbr title="Approved by squad rep"><span class="ms-2 badge bg-success"><i class="fa fa-thumbs-up" aria-hidden="true"></i></span></abbr><?php } else if ($entry['Approved']) { ?><abbr title="Entry automatically approved"><span class="ms-2 badge bg-success"><i class="fa fa-thumbs-up" aria-hidden="true"></i></span></abbr><?php } ?>
                  <?php if ($entry['Refunded'] && $entry['FeeToPay'] > 0) { ?><span class="ms-2 badge bg-warning">PART REFUNDED</span><?php } else if ($entry['Refunded'] && $entry['FeeToPay'] == 0) { ?><span class="ms-2 badge bg-warning">FULLY REFUNDED</span><?php } ?>
                </span>
              </span>
              <span class="d-flex mb-3"><?= htmlspecialchars($entry['GalaName']) ?></span>
            </div>
            <span class="category"><?= htmlspecialchars($entry['GalaVenue']) ?></span>
          </a>
        <?php } while ($entry = $entries->fetch(PDO::FETCH_ASSOC)); ?>
      </div>
    <?php } else { ?>

      <p>You don't have any gala entries at the moment.</p>

      <p>When you make entries, all of your entries are listed here. You can edit entries until either;</p>

      <ul>
        <li>the closing data has passed,</li>
        <li>the gala coordinator has processed your entry or,</li>
        <li>you've paid for the gala entry</li>
      </ul>

      <p>If you need to make changes once your entry has been locked, you will need to contact your gala coordinator.</p>

      <p>
        <a href="<?= autoUrl("galas/entergala") ?>" class="btn btn-success">Enter a gala</a>
      </p>

    <?php } ?>

    <?php if ($timesheet && false) { ?>
      <h2>
        Gala timesheets
      </h2>

      <p class="mb-4">
        Gala Time Sheets give a list of each of your swimmer's entries to a gala
        along with their all-time personal bests and <?= date("Y") ?> personal
        bests.
      </p>

      <div class="news-grid mb-4">
        <?php do { ?>
          <a href="<?= autoUrl("galas/competitions/" . $timesheet['GalaID'] . "/timesheet") ?>">
            <div>
              <span class="title mb-0 justify-content-between align-items-start">
                <span><?= htmlspecialchars($timesheet['GalaName']) ?></span>
              </span>
              <span class="d-flex mb-3"><?= htmlspecialchars($timesheet['GalaVenue']) ?></span>
            </div>
            <span class="category">
              <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
            </span>
          </a>
        <?php } while ($timesheet = $timesheets->fetch(PDO::FETCH_ASSOC)); ?>
      </div>
    <?php } ?>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render(); ?>