<?php

$userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];

$db = app()->db;
$tenant = app()->tenant;

$now = new DateTime('now', new DateTimeZone('Europe/London'));
$nowDay = $now->format('Y-m-d');

$galas = $db->prepare("SELECT GalaID, GalaName, ClosingDate, GalaDate, GalaVenue, CourseLength, CoachEnters FROM galas WHERE Tenant = ? AND GalaDate >= ? ORDER BY GalaDate ASC");
$galas->execute([
  $tenant->getId(),
  $nowDay
]);
$gala = $galas->fetch(PDO::FETCH_ASSOC);
$entriesOpen = false;

$entries = $db->prepare("SELECT EntryID, GalaName, ClosingDate, GalaVenue, MForename, MSurname, EntryProcessed Processed, Charged, Refunded, FeeToPay, Locked, Vetoable, RequiresApproval, Approved FROM ((galaEntries INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) INNER JOIN members ON galaEntries.MemberID = members.MemberID) WHERE GalaDate >= ? AND members.UserID = ?");
$entries->execute([$nowDay, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
$entry = $entries->fetch(PDO::FETCH_ASSOC);

$manualTimeGalas = $db->prepare("SELECT EntryID, GalaName, ClosingDate, GalaVenue, MForename, MSurname, EntryProcessed Processed, Charged, Refunded, FeeToPay, Locked, Vetoable FROM ((galaEntries INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) INNER JOIN members ON galaEntries.MemberID = members.MemberID) WHERE GalaDate >= ? AND members.UserID = ? AND galas.HyTek = 1;");
$manualTimeGalas->execute([$nowDay, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);

$timesheets = $db->prepare("SELECT DISTINCT `galas`.`GalaID`, `GalaName`, `GalaVenue` FROM ((`galas` INNER JOIN `galaEntries` ON `galas`.`GalaID` = `galaEntries`.`GalaID`) INNER JOIN members ON galaEntries.MemberID = members.MemberID) WHERE `GalaDate` >= ? AND members.UserID = ? ORDER BY `GalaDate` ASC");
$timesheets->execute([$nowDay, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
$timesheet = $timesheets->fetch(PDO::FETCH_ASSOC);

$canPayByCard = false;
if (getenv('STRIPE') && $tenant->getStripeAccount()) {
  $canPayByCard = true;
}

$openGalas = false;

// Arrays of swims used to check whever to print the name of the swim entered
// BEWARE This is in an order to ease inputting data into SportSystems, contrary to these arrays in other files
$swimsArray = [
  '25Free' => '25&nbsp;Free',
  '50Free' => '50&nbsp;Free',
  '100Free' => '100&nbsp;Free',
  '200Free' => '200&nbsp;Free',
  '400Free' => '400&nbsp;Free',
  '800Free' => '800&nbsp;Free',
  '1500Free' => '1500&nbsp;Free',
  '25Back' => '25&nbsp;Back',
  '50Back' => '50&nbsp;Back',
  '100Back' => '100&nbsp;Back',
  '200Back' => '200&nbsp;Back',
  '25Breast' => '25&nbsp;Breast',
  '50Breast' => '50&nbsp;Breast',
  '100Breast' => '100&nbsp;Breast',
  '200Breast' => '200&nbsp;Breast',
  '25Fly' => '25&nbsp;Fly',
  '50Fly' => '50&nbsp;Fly',
  '100Fly' => '100&nbsp;Fly',
  '200Fly' => '200&nbsp;Fly',
  '100IM' => '100&nbsp;IM',
  '150IM' => '150&nbsp;IM',
  '200IM' => '200&nbsp;IM',
  '400IM' => '400&nbsp;IM'
];

$strokeCounts = [
  'Free' => 0,
  'Back' => 0,
  'Breast' => 0,
  'Fly' => 0,
  'IM' => 0
];
$distanceCounts = [
  '25' => 0,
  '50' => 0,
  '100' => 0,
  '150' => 0,
  '200' => 0,
  '400' => 0,
  '800' => 0,
  '1500' => 0
];
$chartColours = chartColours(5);
$countEntries = [];
$countEntriesEvents = [];
$countEntriesCount = [];
$countEntriesColours = [];
foreach ($swimsArray as $col => $name) {
  $getCount = $db->prepare("SELECT COUNT(*) FROM galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID WHERE members.UserID = ? AND `" . $col . "` = 1");
  $getCount->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
  $count = $getCount->fetchColumn();
  if ($count > 0) {
    $countEntries[$col]['Name'] = $name;
    $countEntriesEvents[] = html_entity_decode($name);
    $countEntries[$col]['Event'] = $col;
    $countEntries[$col]['Stroke'] = preg_replace("/[^a-zA-Z]+/", "", $col);
    $countEntries[$col]['Distance'] = preg_replace("/[^0-9]/", '', $col);
    $countEntries[$col]['Count'] = $count;
    $countEntriesCount[] = $count;
    $strokeCounts[$countEntries[$col]['Stroke']] += $countEntries[$col]['Count'];
    $distanceCounts[$countEntries[$col]['Distance']] += $countEntries[$col]['Count'];
    if ($countEntries[$col]['Stroke'] == 'Free') {
      $countEntriesColours[] = $chartColours[0];
    } else if ($countEntries[$col]['Stroke'] == 'Back') {
      $countEntriesColours[] = $chartColours[1];
    } else if ($countEntries[$col]['Stroke'] == 'Breast') {
      $countEntriesColours[] = $chartColours[2];
    } else if ($countEntries[$col]['Stroke'] == 'Fly') {
      $countEntriesColours[] = $chartColours[3];
    } else if ($countEntries[$col]['Stroke'] == 'IM') {
      $countEntriesColours[] = $chartColours[4];
    }
  }
}

$strokeCountsData = array_values($strokeCounts);

$pagetitle = "Galas";
include BASE_PATH . "views/header.php";
include "galaMenu.php";
?>

<div class="front-page" style="margin-bottom: -1rem;">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">
          Galas
        </li>
      </ol>
    </nav>


    <h1>Galas</h1>
    <p class="lead">Manage your gala entries</p>

    <?php if ($gala) { ?>
      <h2>
        Upcoming galas
      </h2>

      <div class="news-grid mb-3">
        <?php do {

          $closingDate = new DateTime($gala['ClosingDate']);
          $endDate = new DateTime($gala['GalaDate']);

          if ($now <= $closingDate) {
            $openGalas = true;
          }

        ?>
          <a href="<?= autoUrl("galas/" . $gala['GalaID']) ?>">
            <div>
              <span class="title mb-0 justify-content-between align-items-start">
                <span><?= htmlspecialchars($gala['GalaName']) ?></span>
                <?php if ($now <= $closingDate && !$gala['CoachEnters']) {
                  $entriesOpen = true; ?><span class="ms-2 badge bg-success">ENTRIES OPEN</span><?php } ?>
                <?php if ($now <= $closingDate && $gala['CoachEnters']) { ?><span class="ms-2 badge bg-warning">COACH ENTERS</span><?php } ?>
              </span>
              <span class="d-flex mb-3"><?= htmlspecialchars($gala['GalaVenue']) ?></span>
            </div>
            <?php if ($now <= $closingDate) { ?>
              <span class="category">Entries close at <?= htmlspecialchars($closingDate->format('H:i, j F Y')) ?></span>
            <?php } else { ?>
              <span class="category">Ends on <?= $endDate->format('j F Y') ?></span>
            <?php } ?>
          </a>
        <?php } while ($gala = $galas->fetch(PDO::FETCH_ASSOC)); ?>
      </div>
      <?php if ($openGalas) { ?>
        <p class="mb-4">
          <a href="<?= autoUrl("galas/entergala") ?>" class="btn btn-success">
            Enter a gala
          </a>
        </p>
      <?php } ?>
    <?php } else { ?>

      <div class="row">
        <div class="col-lg-8">
          <div class="alert alert-info">
            <p class="mb-0">
              <strong>There are no upcoming galas at this time</strong>
            </p>
            <p class="mb-0">
              Please check back later.
            </p>
          </div>
        </div>
      </div>

    <?php } ?>

    <?php if ($entry) { ?>
      <h2>
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
    <?php } ?>

    <?php

    $gala = $manualTimeGalas->fetch(PDO::FETCH_ASSOC);

    if ($gala != null) { ?>

      <div class="mb-4">
        <h2>
          Manual times
        </h2>

        <p class="lead">
          Times must be provided manually for the following galas.
        </p>

        <div class="news-grid mb-3">
          <?php do {

            $closingDate = new DateTime($gala['ClosingDate']);

          ?>
            <a href="<?= autoUrl("galas/entries/" . $gala['EntryID'] . '/manual-time') ?>">
              <div>
                <span class="title mb-0 justify-content-between align-items-start">
                  <span><?= htmlspecialchars(\SCDS\Formatting\Names::format($gala['MForename'], $gala['MSurname'])) ?></span>
                </span>
                <span class="d-flex mb-3"><?= htmlspecialchars($gala['GalaName']) ?></span>
              </div>
              <span class="category">
                <div class="btn btn-success">Add or edit times</div>
              </span>
            </a>
          <?php } while ($gala = $manualTimeGalas->fetch(PDO::FETCH_ASSOC)); ?>
        </div>
      </div>

    <?php

    }

    ?>

    <?php if ($canPayByCard) { ?>
      <div class="mb-4">
        <h2>
          Make a payment
        </h2>

        <p class="lead">
          Pay for gala entries by credit or debit card.
        </p>

        <p>
          <a href="<?= autoUrl("galas/pay-for-entries") ?>" class="btn btn-success ">
            Pay now
          </a>
        </p>
      </div>
    <?php } else if (getenv('STRIPE') && $tenant->getStripeAccount()) { ?>
      <div class="mb-4">
        <h2>
          Pay by card
        </h2>

        <p class="lead">
          You can pay for gala entries by credit or debit card.
        </p>

        <p>
          <a href="<?= autoUrl("payments/cards/add") ?>" class="btn btn-success ">
            Add a card
          </a>
        </p>
      </div>
    <?php } ?>

    <?php if ($timesheet && false) { ?>
      <h2>
        Gala timesheets
      </h2>

      <div class="row">
        <div class="col-lg-8">

          <p class="mb-4">
            Gala Time Sheets give a list of each of your swimmer's entries to a gala
            along with their all-time personal bests and <?= date("Y") ?> personal
            bests.
          </p>

          <div class="alert alert-warning">
            <p class="mb-0">
              <strong>
                Gala timesheets are deprecated
              </strong>
            </p>
            <p class="mb-0">
              They will be removed in a future software update
            </p>
          </div>

        </div>
      </div>

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

    <?php if (sizeof($countEntries) > 0) { ?>
      <h2>Statistics</h2>
      <p class="lead mb-4">
        These are statistics for all of your swimmers put together, based on
        entries over all time. Go to <a href="<?= autoUrl('swimmers') ?>">My
          Swimmers</a> to see stats for each swimmer.
      </p>

      <div class="row">
        <div class="col-lg-8">
          <canvas id="eventEntries" class="mb-3"></canvas>
        </div>
        <div class="col-lg-4">
          <canvas id="strokeEntries" class="mb-3"></canvas>
        </div>
      </div>
    <?php } ?>
  </div>
</div>

<?php if (sizeof($countEntries) > 0) { ?>

  <script>
    document.addEventListener("DOMContentLoaded", function(event) {
      var ctx = document.getElementById('eventEntries').getContext('2d');
      var chart = new Chart(ctx, {
        // The type of chart we want to create
        type: 'bar',

        // The data for our dataset
        data: {
          labels: <?= json_encode($countEntriesEvents) ?>,
          datasets: [{
            label: <?= json_encode('All my entries') ?>,
            data: <?= json_encode($countEntriesCount) ?>,
            backgroundColor: <?= json_encode($countEntriesColours) ?>,
          }],
        },

        // Configuration options go here
        options: {
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero: true,
                precision: 0,
              }
            }]
          }
        }
      });

      var ctx = document.getElementById('strokeEntries').getContext('2d');
      var chart = new Chart(ctx, {
        // The type of chart we want to create
        type: 'pie',

        // The data for our dataset
        data: {
          labels: <?= json_encode(['Free', 'Back', 'Breast', 'Fly', 'IM']) ?>,
          datasets: [{
            label: <?= json_encode(html_entity_decode($gala['GalaName'])) ?>,
            data: <?= json_encode($strokeCountsData) ?>,
            backgroundColor: <?= json_encode($chartColours) ?>,
          }],
        },

        // Configuration options go here
        // options: {}
      });
    });
  </script>
<?php } ?>

<?php $footer = new \SCDS\Footer();
$footer->render(); ?>