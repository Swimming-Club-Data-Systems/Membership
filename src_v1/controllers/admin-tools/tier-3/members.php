<?php

use function GuzzleHttp\json_decode;

$db = app()->db;
$tenant = app()->tenant;

$getSquads = $db->prepare("SELECT SquadName, SquadID, SquadFee FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, SquadName ASC;");
$getSquads->execute([
  $tenant->getId(),
]);
$squad = $getSquads->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Members for Tier 3 Billing";

$date = new DateTime('now', new DateTimeZone('Europe/London'));
$dateToday = clone $date;

$fees = $tenant->getKey('TIER3_SQUAD_FEES');
if ($fees) {
  $fees = json_decode($fees, true);
  $date = new DateTime($fees['eighteen_by'], new DateTimeZone('Europe/London'));
} else {
  halt(404);
}

$dateSet = clone $date;
$date->sub(new DateInterval('P18Y'));

$getMembers = $db->prepare("SELECT MForename, MSurname FROM members WHERE Tenant = ? AND DateOfBirth <= ? ORDER BY MForename ASC, MSurname ASC");
$getMembers->execute([
  $tenant->getId(),
  $date->format('Y-m-d'),
]);
$member = $getMembers->fetch(PDO::FETCH_ASSOC);

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin")) ?>">Admin</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin/tier-3")) ?>">Tier 3</a></li>
      <li class="breadcrumb-item active" aria-current="page">Members</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>Tier 3 Billing Members</h1>
      <p class="lead">Members covered by Tier 3 Squad Fees.</p>

      <p>
        Members born on or before <?= htmlspecialchars($date->format('j F Y')) ?>. These members were 18 on or before <?= htmlspecialchars($dateSet->format('j F Y')) ?>.
      </p>

      <?php if ($member) { ?>
        <ul class="list-group">
          <?php do { ?>
            <li class="list-group-item">
              <?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?>
            </li>
          <?php } while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)); ?>
        </ul>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>No members affected</strong>
          </p>
          <p class="mb-0">
            If you expected to see something, check the date is right.
          </p>
        </div>
      <?php } ?>

    </div>

    <div class="col">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/admin-tools/list.json'));
      echo $list->render('tier-three');
      ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
