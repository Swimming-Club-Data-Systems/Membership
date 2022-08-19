<?php

$db = app()->db;
$tenant = app()->tenant;

$disabled = "";

$sql = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE galas.Tenant = ? AND `EntryID` = ? ORDER BY `galas`.`GalaDate` DESC;");
$sql->execute([
  $tenant->getId(),
  $idLast
]);
$row = $sql->fetch(PDO::FETCH_ASSOC);

if ($row == null) {
  halt(404);
}

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && $row['UserID'] != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) {
  halt(404);
}

$swimsArray = ['25Free', '50Free', '100Free', '200Free', '400Free', '800Free', '1500Free', '25Back', '50Back', '100Back', '200Back', '25Breast', '50Breast', '100Breast', '200Breast', '25Fly', '50Fly', '100Fly', '200Fly', '100IM', '150IM', '200IM', '400IM',];
$swimsTextArray = ['25&nbsp;Free', '50&nbsp;Free', '100&nbsp;Free', '200&nbsp;Free', '400&nbsp;Free', '800&nbsp;Free', '1500&nbsp;Free', '25&nbsp;Back', '50&nbsp;Back', '100&nbsp;Back', '200&nbsp;Back', '25&nbsp;Breast', '50&nbsp;Breast', '100&nbsp;Breast', '200&nbsp;Breast', '25&nbsp;Fly', '50&nbsp;Fly', '100&nbsp;Fly', '200&nbsp;Fly', '100&nbsp;IM', '150&nbsp;IM', '200&nbsp;IM', '400&nbsp;IM',];
$swimsTimeArray = ['25FreeTime', '50FreeTime', '100FreeTime', '200FreeTime', '400FreeTime', '800FreeTime', '1500FreeTime', '25BackTime', '50BackTime', '100BackTime', '200BackTime', '25BreastTime', '50BreastTime', '100BreastTime', '200BreastTime', '25FlyTime', '50FlyTime', '100FlyTime', '200FlyTime', '100IMTime', '150IMTime', '200IMTime', '400IMTime',];
$rowArray = [1, null, null, null, null, null, 2, 1,  null, null, 2, 1, null, null, 2, 1, null, null, 2, 1, null, null, 2];
$rowArrayText = ["Freestyle", null, null, null, null, null, 2, "Backstroke",  null, null, 2, "Breaststroke", null, null, 2, "Butterfly", null, null, 2, "Individual Medley", null, null, 2];

$pagetitle = htmlspecialchars(\SCDS\Formatting\Names::format($row['MForename'], $row['MSurname'])) . " - " . htmlspecialchars($row['GalaName']);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <h1><?= htmlspecialchars(\SCDS\Formatting\Names::format($row['MForename'], $row['MSurname'])) ?></h1>
    <p class="lead mb-0">For <?= htmlspecialchars($row['GalaName']) ?>, Closing Date: <?= htmlspecialchars(date('j F Y', strtotime($row['ClosingDate']))) ?></p>
  </div>
</div>

<div class="container-xl">

  <?php

  $closingDate = new DateTime($row['ClosingDate']);
  $theDate = new DateTime('now');
  $closingDate = $closingDate->format('Y-m-d');
  $theDate = $theDate->format('Y-m-d');

  if ($row['EntryProcessed'] == 1 || ($closingDate <= $theDate)) {

  ?>
    <div class="alert alert-warning">
      <strong>We've already processed this gala entry, or our closing date has passed</strong> <br>If you need to make changes, contact the Gala Coordinator directly
    </div>

  <?php
    $disabled .= " onclick=\"return false;\" disabled ";
  } else { ?>
    <h2>Select Swims</h2>
  <?php } ?>

  <form method="post" action="updategala-action">

    <?php for ($i = 0; $i < sizeof($swimsArray); $i++) {
      if ($rowArray[$i] == 1) { ?>
        <div class="row mb-3">
        <?php } ?>
        <div class="col-sm-4 col-md-2">
          <div class="form-check">
            <input type="checkbox" value="1" class="form-check-input" id="<?= $swimsArray[$i] ?>" <?php if ($row[$swimsArray[$i]] == 1) { ?>checked<?php } ?> <?= $disabled ?> name="<?= $swimsArray[$i] ?>">
            <label class="form-check-label" for="<?= $swimsArray[$i] ?>">
              <?= $swimsTextArray[$i] ?>
            </label>
          </div>
        </div>
        <?php if ($rowArray[$i] == 2) { ?>
        </div>
    <?php }
      } ?>

    <input type="hidden" value="0" name="TimesRequired">

    <?php if ($row['EntryProcessed'] == 0 && ($closingDate >= $theDate)) { ?>
      <input type="hidden" value="<?= $row['EntryID'] ?> name=" entryID">
      <p><button type="submit" id="submit" class="btn btn-outline-dark">Update</button></p>
    <?php } ?>

  </form>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
