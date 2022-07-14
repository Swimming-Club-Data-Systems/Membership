<?php

$db = app()->db;
$tenant = app()->tenant;

$galas = $db->prepare("SELECT GalaName, `Description`, ClosingDate, GalaDate, GalaVenue, CourseLength, CoachEnters, RequiresApproval, ProcessingFee FROM galas WHERE Tenant = ? AND GalaID = ?");
$galas->execute([
  $tenant->getId(),
  $id
]);
$gala = $galas->fetch(PDO::FETCH_ASSOC);

if ($gala == null) {
  halt(404);
}

$numEntries = $db->prepare("SELECT COUNT(*) FROM galaEntries WHERE GalaID = ?");
$numEntries->execute([$id]);
$numEntries = $numEntries->fetchColumn();

$amountPaid = $amountLeftToPay = $amountRefunded = $total = 0;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
  $amountPaidQuery = $db->prepare("SELECT SUM(FeeToPay) FROM galaEntries INNER JOIN members ON members.MemberID = galaEntries.MemberID WHERE GalaID = ? AND Charged = ? AND members.UserID = ?");
  $amountPaidQuery->execute([$id, 1, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
  $amountPaid = $amountPaidQuery->fetchColumn();
  $amountPaidQuery->execute([$id, 0, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
  $amountLeftToPay = $amountPaidQuery->fetchColumn();
  $total = $amountPaid + $amountLeftToPay;
  $amountRefunded = $db->prepare("SELECT SUM(AmountRefunded) FROM galaEntries INNER JOIN members ON members.MemberID = galaEntries.MemberID WHERE GalaID = ? AND members.UserID = ?");
  $amountRefunded->execute([$id, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
  $amountRefunded = $amountRefunded->fetchColumn();
} else {
  $amountPaidQuery = $db->prepare("SELECT SUM(FeeToPay) FROM galaEntries WHERE GalaID = ? AND Charged = ?");
  $amountPaidQuery->execute([$id, 1]);
  $amountPaid = $amountPaidQuery->fetchColumn();
  $amountPaidQuery->execute([$id, 0]);
  $amountLeftToPay = $amountPaidQuery->fetchColumn();
  $total = $amountPaid + $amountLeftToPay;
  $amountRefunded = $db->prepare("SELECT SUM(AmountRefunded) FROM galaEntries WHERE GalaID = ?");
  $amountRefunded->execute([$id]);
  $amountRefunded = $amountRefunded->fetchColumn();
}

$entries = $db->prepare("SELECT * FROM galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID WHERE GalaID = ?");
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
  $entries = $db->prepare("SELECT * FROM galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID WHERE GalaID = ? AND UserID = ?");
  $entries->execute([$id, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
} else {
  $entries->execute([$id]);
}

$entry = $entries->fetch(PDO::FETCH_ASSOC);

// Arrays of swims used to check whever to print the name of the swim entered
// BEWARE This is in an order to ease inputting data into SportSystems, contrary to these arrays in other files
$swimsArray = GalaEvents::getEvents();

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
$countEntries = [];
foreach ($swimsArray as $col => $name) {
  $getCount = $db->prepare("SELECT COUNT(*) FROM galaEntries WHERE GalaID = ? AND `" . $col . "` = 1");
  $getCount->execute([$id]);
  $countEntries[$col]['Name'] = $name;
  $countEntries[$col]['Event'] = $col;
  $countEntries[$col]['Stroke'] = preg_replace("/[^a-zA-Z]+/", "", $col);
  $countEntries[$col]['Distance'] = preg_replace("/[^0-9]/", '', $col);
  $countEntries[$col]['Count'] = $getCount->fetchColumn();
  $strokeCounts[$countEntries[$col]['Stroke']] += $countEntries[$col]['Count'];
  $distanceCounts[$countEntries[$col]['Distance']] += $countEntries[$col]['Count'];
}

$markdown = new ParsedownForMembership();
$markdown->setSafeMode(false);

// Get price and event information
$galaData = new GalaPrices($db, $id);

$closingDate = new DateTime($gala['ClosingDate'], new DateTimeZone('Europe/London'));
$finishesBy = new DateTime($gala['GalaDate'], new DateTimeZone('Europe/London'));

$pagetitle = htmlspecialchars($gala['GalaName']) . " - Galas";
include BASE_PATH . "views/header.php";
include "galaMenu.php";
?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas") ?>">Galas</a></li>
        <li class="breadcrumb-item active" aria-current="page">#<?= htmlspecialchars($id) ?></li>
      </ol>
    </nav>
    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          <?= htmlspecialchars($gala['GalaName']) ?>
        </h1>
        <p class="lead mb-0">
          <?= htmlspecialchars($gala['GalaVenue']) ?>
        </p>
      </div>
      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Galas" || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Committee" || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin" || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Coach") { ?>
        <div class="col text-lg-end">
          <div class="d-lg-none mt-3"></div>
          <p>
          <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Gala options
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="<?= autoUrl("galas/$id/edit") ?>">Edit</a>
              <a class="dropdown-item" href="<?= autoUrl("galas/$id/pricing-and-events") ?>">Events and prices</a>
              <a class="dropdown-item" href="<?= autoUrl("galas/$id/sessions") ?>">Sessions</a>
              <?php if (bool($gala['CoachEnters'])) { ?>
                <a class="dropdown-item" href="<?= autoUrl("galas/$id/select-entries") ?>">Manage entries</a>
              <?php } ?>
              <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("galas/$id/team-manager-view.pdf")) ?>">Entry report</a>
              <!--<div class="dropdown-divider"></div>-->
              <?php if ($numEntries > 0 && false) { ?>
                <a class="dropdown-item" href="<?= autoUrl("galas/$id/timesheet") ?>">Timesheet</a>
              <?php } ?>
              <?php if ($numEntries > 0) { ?>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("galas/$id/photography-permissions.pdf")) ?>">Photography permissions</a>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("galas/$id/create-registers")) ?>">Create Registers</a>
              <?php } ?>
            </div>
          </div>
          </p>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

<div class="container-xl">
  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['GalaAddedSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['GalaAddedSuccess']) { ?>
    <div class="alert alert-success">We've successfully added this gala</div>
  <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['GalaAddedSuccess']);
  } ?>

  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['CreateRegisterSuccessStatus']) && $_SESSION['TENANT-' . app()->tenant->getId()]['CreateRegisterSuccessStatus']) { ?>
    <div class="alert alert-success">We've successfully created registers for this gala</div>
  <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['CreateRegisterSuccessStatus']);
  } ?>

  <h2>About this gala</h2>

  <?= $markdown->text($gala['Description']) ?>

  <div class="row">
    <div class="col-sm-6 col-md-4">
      <h3 class="h6">Gala Name</h3>
      <p><?= htmlspecialchars($gala['GalaName']) ?></p>
    </div>

    <div class="col-sm-6 col-md-4">
      <h3 class="h6">Venue</h3>
      <p><?= htmlspecialchars($gala['GalaVenue']) ?></p>
    </div>

    <div class="col-sm-6 col-md-4">
      <h3 class="h6">Category</h3>
      <?php if ($gala['CourseLength'] == "LONG") { ?>
        <p>This is a <strong>Long Course</strong> gala</p>
      <?php } else if ($gala['CourseLength'] == "SHORT") { ?>
        <p>This is a <strong>Short Course</strong> gala</p>
      <?php } else { ?>
        <p>This gala is neither short course or long course</p>
      <?php } ?>
    </div>

    <div class="col-sm-6 col-md-4">
      <h3 class="h6">Closing date</h3>
      <p><?= htmlspecialchars($closingDate->format("H:i, j F Y")) ?></p>
    </div>

    <div class="col-sm-6 col-md-4">
      <h3 class="h6">Finishes by</h3>
      <p><?= htmlspecialchars($finishesBy->format("j F Y")) ?></p>
    </div>

    <?php if ($gala['CoachEnters']) { ?>
      <div class="col-sm-6 col-md-4">
        <h3 class="h6">Coach enters</h3>
        <p>Coaches make all entries for this gala</p>
      </div>
    <?php } ?>

    <?php if ($gala['RequiresApproval']) { ?>
      <div class="col-sm-6 col-md-4">
        <h3 class="h6">Requires approval</h3>
        <p>Squad reps must approve entries for this gala. If a squad has no reps, entries are approved automatically</p>
      </div>
    <?php } ?>

    <div class="col-sm-6 col-md-4">
      <h3 class="h6">Number of entries</h3>
      <p><?= $numEntries ?></p>
    </div>

    <?php if ($entry != null) { ?>

      <div class="col-sm-6 col-md-4">
        <h3 class="h6"><?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') { ?>Total cost of your entries<?php } else { ?>Total cost of entries<?php } ?></h3>
        <p>&pound;<?= number_format($total, 2) ?></p>
      </div>

      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent' && $amountLeftToPay > 0) { ?>
        <div class="col-sm-6 col-md-4">
          <h3 class="h6">Total left to charge</h3>
          <p>&pound;<?= number_format($amountLeftToPay, 2) ?></p>
        </div>
      <?php } else if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent' && $amountRefunded > 0) { ?>
        <div class="col-sm-6 col-md-4">
          <h3 class="h6">Total after refunds</h3>
          <p>&pound;<?= number_format($total - ($amountRefunded / 100), 2) ?></p>
        </div>
      <?php } ?>

      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && $amountRefunded > 0) { ?>
        <div class="col-sm-6 col-md-4">
          <h3 class="h6">Total payable after refunds</h3>
          <p>&pound;<?= number_format($total - ($amountRefunded / 100), 2) ?></p>
        </div>
      <?php } ?>

      <div class="col-sm-6 col-md-4">
        <h3 class="h6"><?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') { ?>Total refunded to you<?php } else { ?>Total refunded to parents<?php } ?></h3>
        <p>&pound;<?= htmlspecialchars(MoneyHelpers::intToDecimal($amountRefunded)) ?></p>
      </div>

    <?php } ?>

    <?php if ($gala['ProcessingFee'] > 0) { ?>
      <div class="col-sm-6 col-md-4">
        <h3 class="h6">Per entry processing fee</h3>
        <p>&pound;<?= htmlspecialchars(MoneyHelpers::intToDecimal($gala['ProcessingFee'])) ?></p>
      </div>
    <?php } ?>
  </div>

  <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && $gala['CoachEnters']) { ?>
    <h2>Entries are managed by your coach</h2>
    <p class="lead">Let them know you can enter this gala</p>
    <p>
      <a href="<?= autoUrl("galas/" . $id . "/indicate-availability") ?>" class="btn btn-success">
        Indicate availability
      </a>
    </p>
  <?php } else if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Coach' || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Galas' || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>

    <h2>Manage events and prices</h2>
    <p class="lead">Select which events are running and enter the price for each event</p>
    <p>
      <a href="<?= autoUrl("galas/" . $id . "/pricing-and-events") ?>" class="btn btn-success">
        Manage prices
      </a>
    </p>

    <h2>Manage sessions</h2>
    <p class="lead">Add sessions <?php if ($gala['CoachEnters']) { ?>to this gala so parents can indicate availability or <?php } ?>so that you can take registers</p>
    <p>
      <a href="<?= autoUrl("galas/" . $id . "/sessions") ?>" class="btn btn-success">
        Manage sessions
      </a>
    </p>

    <h2>Entry report</h2>
    <p class="lead">Export a PDF entry report that can be shared with parents</p>
    <p><a href="<?= htmlspecialchars(autoUrl("galas/" . $id . "/team-manager-view.pdf")) ?>" class="btn btn-success">Export PDF</a></p>

    <?php if (bool($gala['CoachEnters'])) { ?>
      <h2>Manage entries</h2>
      <p class="lead">Add and edit entries for all competing swimmers</p>
      <p>
        <a href="<?= autoUrl("galas/" . $id . "/select-entries") ?>" class="btn btn-success">
          Manage entries
        </a>
      </p>
    <?php } ?>
  <?php } ?>

  <!-- Gala timesheets temporarily hidden -->
  <?php if ($numEntries > 0 && false) { ?>
    <h2>Gala timesheet</h2>
    <p class="lead">Download a timesheet for this gala.</p>
    <p>Gala timesheets give a list of each swimmer's entries to a gala along with their all-time personal bests and
      <?= date("Y") ?> personal bests.</p>
    <p>
      <a class="btn btn-success" href="<?= autoUrl("galas/" . $id . "/timesheet") ?>">Download timesheet</a>
    </p>

    <h2>Charts</h2>
    <p class="lead">An easy overview of entry data.</p>
    <div class="row">
      <div class="col-lg-8">
        <canvas id="eventEntries" class="mb-3"></canvas>
      </div>
      <div class="col-lg-4">
        <canvas id="strokeEntries" class="mb-3"></canvas>
      </div>
    </div>
  <?php } ?>

  <h2>Entries</h2>
  <p class="lead">
    There have been <?= $numEntries ?> entries to this gala
  </p>

  <?php

  if ($entry != null) { ?>

    <div class="row">
      <?php do { ?>
        <div class="col-md-6 col-lg-4">
          <h3>
            <?= htmlspecialchars(\SCDS\Formatting\Names::format($entry['MForename'], $entry['MSurname'])) ?>
          </h3>

          <p>
            <small class="text-muted">
              <strong>Date of Birth:</strong>&nbsp;<?= date('d/m/Y', strtotime($entry['DateOfBirth'])) ?>,
              <strong>Swim&nbsp;England:</strong>&nbsp;<?= htmlspecialchars($entry['ASANumber']) ?>
            </small>
          </p>

          <p>
            <a href="<?= autoUrl("galas/entries/" . $entry['EntryID'] . "/") ?>">Edit</a>
          </p>

          <ul>
            <?php foreach ($swimsArray as $event => $name) { ?>
              <?php if ($entry[$event]) { ?>
                <li><?= $name ?></li>
              <?php } ?>
            <?php } ?>
          </ul>
        </div>
      <?php } while ($entry = $entries->fetch(PDO::FETCH_ASSOC)); ?>
    </div>

  <?php } ?>

</div>


<?php $chartColours = chartColours(5); ?>
<script>
  document.addEventListener("DOMContentLoaded", function(event) {
    var ctx = document.getElementById('eventEntries').getContext('2d');
    var chart = new Chart(ctx, {
      // The type of chart we want to create
      type: 'bar',

      // The data for our dataset
      data: {
        labels: [<?php
                  foreach ($countEntries as $key => $event) {
                    if ($event['Count'] > 0) {
                  ?> <?= json_encode(html_entity_decode($event['Name'])) ?>, <?php
                                                                            }
                                                                          } ?>],
        datasets: [{
          label: <?= json_encode(html_entity_decode($gala['GalaName'])) ?>,
          data: [<?php
                  foreach ($countEntries as $key => $event) {
                    if ($event['Count'] > 0) {
                  ?><?= $event['Count'] ?>, <?php
                                          }
                                        } ?>],
          backgroundColor: [<?php
                            foreach ($countEntries as $key => $event) {
                              if ($event['Count'] > 0) {
                                if ($event['Stroke'] == 'Free') {
                            ?> <?= json_encode($chartColours[0]) ?>
                <?php
                                } else if ($event['Stroke'] == 'Back') {
                ?> <?= json_encode($chartColours[1]) ?>
                <?php
                                } else if ($event['Stroke'] == 'Breast') {
                ?> <?= json_encode($chartColours[2]) ?>
                <?php
                                } else if ($event['Stroke'] == 'Fly') {
                ?> <?= json_encode($chartColours[3]) ?>
                <?php
                                } else if ($event['Stroke'] == 'IM') {
                ?> <?= json_encode($chartColours[4]) ?>
                <?php
                                }
                ?>, <?php
                              }
                            } ?>
          ],
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
        labels: [<?php
                  foreach ($strokeCounts as $stroke => $count) {
                    if ($count > 0) {
                  ?> <?= json_encode($stroke) ?>, <?php
                                                }
                                              } ?>],
        datasets: [{
          label: <?= json_encode(html_entity_decode($gala['GalaName'])) ?>,
          data: [<?php
                  foreach ($strokeCounts as $stroke => $count) { ?> "<?= $count ?>", <?php } ?>],
          backgroundColor: <?= json_encode(chartColours(5)) ?>,
        }],
      },

      // Configuration options go here
      // options: {}
    });
  });
</script>

<?php $footer = new \SCDS\Footer();
$footer->render(); ?>