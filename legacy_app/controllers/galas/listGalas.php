<?php

$pagetitle = "Galas";

$now = new DateTime('now', new DateTimeZone('Europe/London'));
$nowDay = $now->format('Y-m-d');

$db = app()->db;
$tenant = app()->tenant;

$galas = $db->prepare("SELECT GalaID, GalaName, ClosingDate, GalaDate, GalaVenue, CourseLength FROM galas WHERE Tenant = ? AND GalaDate >= ?");
$galas->execute([
  $tenant->getId(),
  $nowDay
]);
$gala = $galas->fetch(PDO::FETCH_ASSOC);

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
  $getCount = null;
  if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
    $getCount = $db->prepare("SELECT COUNT(*) FROM galaEntries INNER JOIN galas ON galas.GalaID = galaEntries.GalaID WHERE `" . $col . "` = 1 AND galas.Tenant = ?");
    $getCount->execute([$tenant->getId()]);
  } else {
    $getCount = $db->prepare("SELECT COUNT(*) FROM galaEntries INNER JOIN galas ON galas.GalaID = galaEntries.GalaID WHERE `" . $col . "` = 1 AND galas.Tenant = ?");
    $getCount->execute([$tenant->getId()]);
  }
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

$now = new DateTime('now', new DateTimeZone('Europe/London'));

include BASE_PATH . "views/header.php";
include "galaMenu.php"; ?>
<div class="front-page" style="margin-bottom: -1rem;">
  <div class="container-xl">
    <h1>Galas</h1>
    <p class="lead">Gala Entry Management at <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?></p>

      <h2 class="mb-4">
        Upcoming Galas
      </h2>

      <?php if ($gala) { ?>
      <div class="news-grid mb-4">
        <?php do {
          $closingDate = new DateTime($gala['ClosingDate']);
          $endDate = new DateTime($gala['GalaDate']);

          ?>
          <a href="<?=autoUrl("galas/" . $gala['GalaID'])?>">
            <div>
              <span class="title mb-0 justify-content-between align-items-start">
                <span><?=htmlspecialchars($gala['GalaName'])?></span>
                <?php if ($now <= $closingDate) {?><span class="ms-2 badge bg-primary">ENTRIES OPEN</span><?php } ?>
              </span>
              <span class="d-flex mb-3"><?=htmlspecialchars($gala['GalaVenue'])?></span>
            </div>
            <?php if ($now <= $closingDate) { ?>
            <span class="category">Entries close at <?= htmlspecialchars($closingDate->format('H:i, j F Y')) ?></span>
            <?php } else { ?>
            <span class="category">Ends on <?=$endDate->format('j F Y')?></span>
            <?php } ?>
          </a>
        <?php } while ($gala = $galas->fetch(PDO::FETCH_ASSOC)); ?>
      </div>
      <?php } ?>

      <p>
        <a href="<?=autoUrl("galas/addgala")?>" class="btn btn-success">
          Add Gala
        </a>
      </p>

      <!--

      <div class="row">
        <div class="col-md-5 py-4">
          <div class="panel bg-white">
            <h3 class="panel-title bg-warning text-dark">
              Warning!
            </h3>

            <p>
              For HyTek galas, parents will be asked to manually enter times after selecting swims.
            </p>
          </div>
        </div>
      </div>

      -->
      
    <!--
    GALA TIME SHEETS (DEPRECATED FOR NOW)
    -->
    <!--
    <div class="mb-4">
			<div class="row">
			  <div class="col-lg-8">
					<h2>Generate a Gala Time Sheet</h2>
					<p class="lead">
						Gala Time Sheets give a list of each swimmer's entries to a gala along with their all-time personal bests and <?=date("Y")?> personal bests.
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

					<?php
          $sql = $db->prepare("SELECT DISTINCT `galas`.`GalaID`, `GalaName` FROM `galas` INNER JOIN `galaEntries` ON `galas`.`GalaID` = `galaEntries`.`GalaID` WHERE Tenant = ? AND `GalaDate` >= ? ORDER BY `GalaDate` ASC;");
          $sql->execute([
            $tenant->getId(),
            $nowDay
          ]);
					$row = $sql->fetch(PDO::FETCH_ASSOC);
					if ($row != null) {
						?><ul class="list-unstyled mb-0"><?php
						do { ?>
							<li>
								<a href="<?php echo autoUrl("galas/" . $row['GalaID'] .
								"/timesheet"); ?>" target="_blank"><?php echo $row['GalaName']; ?></a>
							</li>
							<?php
						} while ($row = $sql->fetch(PDO::FETCH_ASSOC));
						?></ul><?php
					} else {
						?><p class="mb-0">There are no galas with corresponding entries.</p><?php
					}?>
				</div>
			</div>
    </div>
    -->

  	<?php if (sizeof($countEntries) > 0) { ?>
  				<div class="mb-4">
  				  <h2>Statistics</h2>
  				  <p class="lead">These statistics are gathered from all previous gala entries in our system</p>
    		    <div class="row">
							<div class="col-lg-8">
								<canvas id="eventEntries" class="mb-3"></canvas>
							</div>
							<div class="col-lg-4">
								<canvas id="strokeEntries" class="mb-3"></canvas>
							</div>
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
      labels: <?=json_encode($countEntriesEvents)?>,
      datasets: [{
        label: <?=json_encode('All entries')?>,
        data: <?=json_encode($countEntriesCount)?>,
        backgroundColor: <?=json_encode($countEntriesColours)?>,
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
      labels: <?=json_encode(['Free', 'Back', 'Breast', 'Fly', 'IM'])?>,
      datasets: [{
        label: <?=json_encode(html_entity_decode($gala['GalaName']))?>,
        data: <?=json_encode($strokeCountsData)?>,
        backgroundColor: <?=json_encode($chartColours)?>,
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
