<?php

$db = app()->db;
$tenant = app()->tenant;

$swimmer = $db->prepare("SELECT MForename, MSurname, UserID FROM members WHERE Tenant = ? AND MemberID = ?");
$swimmer->execute([
  $tenant->getId(),
  $id
]);
$swimmer = $swimmer->fetch(PDO::FETCH_ASSOC);

if ($swimmer == null) {
  halt(404);
}

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && $swimmer['UserID'] !== $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) {
	halt(404);
}

$strokes = [
  1 => 'Free',
  2 => 'Back',
  3 => 'Breast',
  4 => 'Butterfly',
  5 => 'IM'
];

$distances = [
  1 => [
    50,
    100,
    200,
    400,
    800,
    1500
  ],
  2 => [
    50,
    100,
    200
  ],
  3 => [
    50,
    100,
    200
  ],
  4 => [
    50,
    100,
    200
  ],
  5 => [
    100,
    200,
    400
  ],
];

$getTime = $db->prepare("SELECT `Time`, `Date`, `Round`, `Stroke`, `Distance`, `Name`, `City`, `GalaName` FROM ((meetResults INNER JOIN meetsWithResults ON meetResults.Meet = meetsWithResults.Meet) LEFT JOIN galas ON meetsWithResults.Gala = galas.GalaID) WHERE `Time` NOT IN ('NT', 'NS', 'DNF', 'DQ', 'SCR') AND Member = ? AND Stroke = ? AND Distance = ? AND meetResults.Course = ? ORDER BY IntTime ASC LIMIT 1");
$getTime->execute([$id, 1, 200, 'S']);

$pagetitle = htmlspecialchars(\SCDS\Formatting\Names::format($swimmer['MForename'], $swimmer['MSurname'])) . ' Times';

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?=autoUrl("members")?>">Members</a></li>
			<li class="breadcrumb-item"><a href="<?=autoUrl("members/" . $id)?>">#<?=htmlspecialchars($id)?></a></li>
			<li class="breadcrumb-item active" aria-current="page">Best times</li>
		</ol>
	</nav>

  <h1>Best times</h1>
  <p class="lead">Welcome to our new best times system.</p>
  <p>Our new system uses a results file for every gala so has only been populated with results going back a reasonable period of time.</p>

  <div class="mb-3">
    <h2>Short course</h2>
    <div class="py-1 px-2 bg-primary text-white">
      <div class="row">
        <div class="col-4 col-sm-3 col-lg-2">
          <strong>Event</strong>
        </div>
        <div class="col-4 col-sm-3 col-md-2 text-end">
          <strong>Time</strong>
        </div>
        <div class="col-4 col-sm-3 col-md-2 text-truncate text-end text-lg-center">
          <strong>Date</strong>
        </div>
        <div class="col-8 col-sm-5 col-lg-4 text-truncate d-none d-md-block">
          <strong>Gala</strong>
        </div>
        <div class="col-4 col-sm-3 text-end text-lg-start col-lg-2 text-truncate d-none d-sm-block d-md-none d-lg-block">
          <strong>City</strong>
        </div>
      </div>
    </div>
    <?php $count = 0; ?>
    <?php foreach ($strokes as $strokeCode => $stroke) { ?>
      <?php foreach ($distances[$strokeCode] as $distance) {
        $getTime->execute([$id, $strokeCode, $distance, 'S']);
        $row = $getTime->fetch(PDO::FETCH_ASSOC);
        if ($row != null) { ?>
        <div class="py-1 px-2  <?php if ($count%2 == 0) { ?>bg-light<?php } ?>">
          <div class="row">
            <div class="col-4 col-sm-3 col-lg-2 text-truncate">
              <a href="<?=htmlspecialchars(autoUrl("members/" . $id . "/times/event?course=S&stroke=" . $strokeCode . "&distance=" . $distance))?>"><?=$distance?>m <?=$stroke?></a>
            </div>
            <div class="col-4 col-sm-3 col-md-2 text-end">
              <span class="font-monospace"><?=htmlspecialchars($row['Time'])?></span>
            </div>
            <div class="col-4 col-sm-3 col-md-2 text-truncate text-end text-lg-center">
              <?=htmlspecialchars((new DateTime($row['Date'], new DateTimeZone('Europe/London')))->format("d/m/Y"))?>
            </div>
            <div class="col-8 col-sm-5 col-lg-4 text-truncate d-none d-md-block" title="<?php if ($row['GalaName'] == null) { ?><?=htmlspecialchars($row['Name'])?><?php } else { ?><?=htmlspecialchars($row['GalaName'])?><?php } ?>">
              <?php if ($row['GalaName'] == null) { ?>
              <?=htmlspecialchars($row['Name'])?><?php if (mb_strlen($row['Name']) == 30) { ?>&hellip;<?php } ?>
              <?php } else { ?>
              <?=htmlspecialchars($row['GalaName'])?>
              <?php } ?>
            </div>
            <div class="col-4 col-sm-3 text-end text-lg-start col-lg-2 text-truncate d-none d-sm-block d-md-none d-lg-block">
              <?=htmlspecialchars($row['City'])?>
            </div>
          </div>
        </div>
        <?php $count++; } ?>
      <?php } ?>
    <?php } ?>
  </div>

  <div class="mb-3">
    <h2>Long course</h2>
    <div class="py-1 px-2 bg-primary text-white">
      <div class="row">
        <div class="col-4 col-sm-3 col-lg-2">
          <strong>Event</strong>
        </div>
        <div class="col-4 col-sm-3 col-md-2 text-end">
          <strong>Time</strong>
        </div>
        <div class="col-4 col-sm-3 col-md-2 text-truncate text-end text-lg-center">
          <strong>Date</strong>
        </div>
        <div class="col-8 col-sm-5 col-lg-4 text-truncate d-none d-md-block">
          <strong>Gala</strong>
        </div>
        <div class="col-4 col-sm-3 text-end text-lg-start col-lg-2 text-truncate d-none d-sm-block d-md-none d-lg-block">
          <strong>City</strong>
        </div>
      </div>
    </div>
    <?php $count = 0; ?>
    <?php foreach ($strokes as $strokeCode => $stroke) { ?>
      <?php foreach ($distances[$strokeCode] as $distance) {
        $getTime->execute([$id, $strokeCode, $distance, 'L']);
        $row = $getTime->fetch(PDO::FETCH_ASSOC);
        if ($row != null) { ?>
        <div class="py-1 px-2  <?php if ($count%2 == 0) { ?>bg-light<?php } ?>">
          <div class="row">
            <div class="col-4 col-sm-3 col-lg-2 text-truncate">
              <a href="<?=htmlspecialchars(autoUrl("members/" . $id . "/times/event?course=L&stroke=" . $strokeCode . "&distance=" . $distance))?>"><?=$distance?>m <?=$stroke?></a>
            </div>
            <div class="col-4 col-sm-3 col-md-2 text-end">
              <span class="font-monospace"><?=htmlspecialchars($row['Time'])?></span>
            </div>
            <div class="col-4 col-sm-3 col-md-2 text-truncate text-end text-lg-center">
              <?=htmlspecialchars((new DateTime($row['Date'], new DateTimeZone('Europe/London')))->format("d/m/Y"))?>
            </div>
            <div class="col-8 col-sm-5 col-lg-4 text-truncate d-none d-md-block" title="<?php if ($row['GalaName'] == null) { ?><?=htmlspecialchars($row['Name'])?><?php } else { ?><?=htmlspecialchars($row['GalaName'])?><?php } ?>">
              <?php if ($row['GalaName'] == null) { ?>
              <?=htmlspecialchars($row['Name'])?><?php if (mb_strlen($row['Name']) == 30) { ?>&hellip;<?php } ?>
              <?php } else { ?>
              <?=htmlspecialchars($row['GalaName'])?>
              <?php } ?>
            </div>
            <div class="col-4 col-sm-3 text-end text-lg-start col-lg-2 text-truncate d-none d-sm-block d-md-none d-lg-block">
              <?=htmlspecialchars($row['City'])?>
            </div>
          </div>
        </div>
        <?php $count++; } ?>
      <?php } ?>
    <?php } ?>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
