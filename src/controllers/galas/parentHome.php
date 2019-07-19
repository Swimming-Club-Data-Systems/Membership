<?php

$userID = $_SESSION['UserID'];

global $db;

$galas = $db->query("SELECT GalaID, GalaName, ClosingDate, GalaDate, GalaVenue, CourseLength, CoachEnters FROM galas WHERE GalaDate >= CURDATE() ORDER BY GalaDate ASC");
$gala = $galas->fetch(PDO::FETCH_ASSOC);
$entriesOpen = false;

$entries = $db->prepare("SELECT EntryID, GalaName, ClosingDate, GalaVenue, MForename, MSurname, EntryProcessed Processed, Charged, Refunded, FeeToPay, Locked, Vetoable FROM ((galaEntries INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) INNER JOIN members ON galaEntries.MemberID = members.MemberID) WHERE GalaDate >= CURDATE() AND members.UserID = ?");
$entries->execute([$_SESSION['UserID']]);
$entry = $entries->fetch(PDO::FETCH_ASSOC);

$timesheets = $db->prepare("SELECT DISTINCT `galas`.`GalaID`, `GalaName`, `GalaVenue` FROM ((`galas` INNER JOIN `galaEntries` ON `galas`.`GalaID` = `galaEntries`.`GalaID`) INNER JOIN members ON galaEntries.MemberID = members.MemberID) WHERE `GalaDate` >= CURDATE() AND members.UserID = ? ORDER BY `GalaDate` ASC");
$timesheets->execute([$_SESSION['UserID']]);
$timesheet = $timesheets->fetch(PDO::FETCH_ASSOC);

/* Stats Section */
$swimsCountArray = [];
$strokesCountArray = [0, 0, 0, 0, 0];
$strokesCountTextArray = ["Freestyle", "Breaststroke", "Butterfly", "Backstroke", "Individual Medley"];
$swimsArray = ['50Free','100Free','200Free','400Free','800Free','1500Free','50Breast','100Breast','200Breast','50Fly','100Fly','200Fly','50Back','100Back','200Back','100IM','150IM','200IM','400IM',];
$strokesArray = ['0','0','0','0','0','0','1','1','1','2','2','2','3','3','3','4','4','4','4',];
$swimsTextArray = ['50 Free','100 Free','200 Free','400 Free','800 Free','1500 Free','50 Breast','100 Breast','200 Breast','50 Fly','100 Fly','200 Fly','50 Back','100 Back','200 Back','100 IM','150 IM','200 IM','400 IM',];
$statsCounter = 0;
for ($i=0; $i<sizeof($swimsArray); $i++) {
  $col = $swimsArray[$i];
  $getTimes = $db->prepare("SELECT COUNT(*) FROM (galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) WHERE `UserID` = ? AND `$col` = '1'");
  $getTimes->execute([$_SESSION['UserID']]);
  $count = $getTimes->fetchColumn();
  $swimsCountArray[$i] = $count;
  $strokesCountArray[$strokesArray[$i]] += $count;
  $statsCounter += $count;
}

$canPayByCard = false;
if (env('STRIPE')) {
  $canPayByCard = true;
}

$openGalas = false;

$pagetitle = "Galas";
include BASE_PATH . "views/header.php";
include "galaMenu.php";
?>

<div class="front-page" style="margin-bottom: -1rem;">
  <div class="container">
    <h1>Galas</h1>
    <p class="lead">Manage your gala entries</p>

    <?php if ($gala) { ?>
    <h2 class="mb-4">
      Upcoming galas
    </h2>

    <div class="news-grid mb-3">
      <?php do {
        $now = new DateTime();
        $closingDate = new DateTime($gala['ClosingDate']);
        $endDate = new DateTime($gala['GalaDate']);

        if ($now <= $closingDate) {
          $openGalas = true;
        }

        ?>
        <a href="<?=autoUrl("galas/" . $gala['GalaID'])?>">
          <div>
            <span class="title mb-0 justify-content-between align-items-start">
              <span><?=htmlspecialchars($gala['GalaName'])?></span>
              <?php if ($now <= $closingDate && !$gala['CoachEnters']) { $entriesOpen = true;?><span class="ml-2 badge badge-success">ENTRIES OPEN</span><?php } ?>
              <?php if ($now <= $closingDate && $gala['CoachEnters']) { ?><span class="ml-2 badge badge-warning">COACH ENTERS</span><?php } ?>
            </span>
            <span class="d-flex mb-3"><?=htmlspecialchars($gala['GalaVenue'])?></span>
          </div>
          <?php if ($now <= $closingDate) { ?>
          <span class="category">Entries close on <?=$closingDate->format('j F Y')?></span>
          <?php } else { ?>
          <span class="category">Ends on <?=$endDate->format('j F Y')?></span>
          <?php } ?>
        </a>
      <?php } while ($gala = $galas->fetch(PDO::FETCH_ASSOC)); ?>
    </div>
    <?php if ($openGalas) { ?>
    <p class="mb-4">
      <a href="<?=autoUrl("galas/entergala")?>" class="btn btn-success">
        Enter a gala
      </a>
    </p>
    <?php } ?>
    <?php } ?>

    <?php if ($canPayByCard) { ?>
    <div class="mb-4">
      <h2 class="mb-4">
        Make a payment
      </h2>

      <p class="lead">
        Pay for gala entries by credit or debit card.
      </p>

      <p>
        <a href="<?=autoUrl("galas/pay-for-entries")?>" class="btn btn-success ">
          Pay now
        </a>
      </p>
    </div>
    <?php } else if (env('STRIPE')) { ?>
      <div class="mb-4">
      <h2 class="mb-4">
        Pay by card
      </h2>

      <p class="lead">
        You can pay for gala entries by credit or debit card.
      </p>

      <p>
        <a href="<?=autoUrl("payments/cards/add")?>" class="btn btn-success ">
          Add a card
        </a>
      </p>
    </div>
    <?php } ?>

    <?php if ($entry) { ?>
    <h2 class="mb-4">
      Your gala entries
    </h2>

    <div class="news-grid mb-4">
      <?php do {
        $now = new DateTime();
        $closingDate = new DateTime($entry['ClosingDate']);

        ?>
        <a href="<?=autoUrl("galas/entries/" . $entry['EntryID'])?>">
          <div>
            <span class="title mb-0 justify-content-between align-items-start">
              <span><?=htmlspecialchars($entry['MForename'] . ' ' . $entry['MSurname'])?></span>
              <span class="text-right">
                <?php if ($now <= $closingDate && !$entry['Charged'] && !$entry['Processed'] && !$entry['Locked']) {?><span class="ml-2 badge badge-success">EDITABLE</span><?php } ?>
                <?php if ($entry['Charged']) {?><span class="ml-2 badge badge-warning"><i class="fa fa-money" aria-hidden="true"></i> PAID</span><?php } ?>
                <?php if ($entry['Vetoable']) {?><span class="ml-2 badge badge-info">VETOABLE</span><?php } ?>
                <?php if ($entry['Refunded'] && $entry['FeeToPay'] > 0) {?><span class="ml-2 badge badge-warning">PART REFUNDED</span><?php } else if ($entry['Refunded'] && $entry['FeeToPay'] == 0) {?><span class="ml-2 badge badge-warning">FULLY REFUNDED</span><?php } ?>
              </span>
            </span>
            <span class="d-flex mb-3"><?=htmlspecialchars($entry['GalaName'])?></span>
          </div>
          <span class="category"><?=htmlspecialchars($entry['GalaVenue'])?></span>
        </a>
      <?php } while ($entry = $entries->fetch(PDO::FETCH_ASSOC)); ?>
    </div>
    <?php } ?>

    <?php if ($timesheet) { ?>
    <h2>
      Gala timesheets
    </h2>

    <p class="mb-4">
      Gala Time Sheets give a list of each of your swimmer's entries to a gala
      along with their all-time personal bests and <?=date("Y")?> personal
      bests.
    </p>

    <div class="news-grid mb-4">
      <?php do { ?>
        <a href="<?=autoUrl("galas/competitions/" . $timesheet['GalaID'] . "/timesheet")?>">
          <div>
            <span class="title mb-0 justify-content-between align-items-start">
              <span><?=htmlspecialchars($timesheet['GalaName'])?></span>
            </span>
            <span class="d-flex mb-3"><?=htmlspecialchars($timesheet['GalaVenue'])?></span>
          </div>
          <span class="category">
            <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
          </span>
        </a>
      <?php } while ($timesheet = $timesheets->fetch(PDO::FETCH_ASSOC)); ?>
    </div>
    <?php } ?>

    <?php if ($statsCounter>0) { ?>
    <h2>Statistics</h2>
    <p class="mb-4">
      These are statistics for all of your swimmers put together, based on
      entries over all time. Go to <a href="<?=autoUrl('swimmers')?>">My
      Swimmers</a> to see stats for each swimmer.
    </p>

    <div class="chart mb-3" id="piechart"></div>
    <div class="chart mb-3" id="barchart"></div>
    <?php } ?>
  </div>
</div>

<?php if ($statsCounter>0) { ?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
  google.charts.load('current', {'packages':['corechart']});

  google.charts.setOnLoadCallback(drawPieChart);
  google.charts.setOnLoadCallback(drawBarChart);

  function drawPieChart() {

    var data = google.visualization.arrayToDataTable([
      ['Stroke', 'Total Number of Entries'],
      <?php for ($i=0; $i<sizeof($strokesCountArray); $i++) { ?>
        ['<?php echo $strokesCountTextArray[$i]; ?>', <?php echo $strokesCountArray[$i]; ?>],
      <?php } ?>
    ]);

    var options = {
      title: 'My Gala Entries by Stroke',
      fontName: 'Open Sans',
      backgroundColor: {
        fill:'transparent'
      },
      chartArea: {
        left: '0',
        right: '0',
      }
    };

    var chart = new google.visualization.PieChart(document.getElementById('piechart'));

    chart.draw(data, options);
  }
  function drawBarChart() {

    var data = google.visualization.arrayToDataTable([
      ['Stroke', 'Total Number of Entries'],
      <?php for ($i=0; $i<sizeof($swimsArray); $i++) {
        if ($swimsCountArray[$i] > 0) { ?>
          ['<?php echo $swimsTextArray[$i]; ?>', <?php echo $swimsCountArray[$i]; ?>],
        <?php }
      } ?>
    ]);

    var options = {
      title: 'My Gala Entries by Event',
      fontName: 'Open Sans',
      backgroundColor: {
        fill:'transparent'
      },
      chartArea: {
        left: '0',
        right: '0',
      },
      backgroundColor: {
        fill:'transparent'
      },
      legend: {
        position: 'none',
      }
    };

    var chart = new google.visualization.ColumnChart(document.getElementById('barchart'));

    chart.draw(data, options);
  }
</script>
<?php } ?>

<?php include BASE_PATH . "views/footer.php"; ?>
