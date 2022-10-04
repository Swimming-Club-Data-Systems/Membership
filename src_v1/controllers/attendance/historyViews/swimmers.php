<?php

$db = app()->db;
$tenant = app()->tenant;

$pagetitle = "Attendance History by Swimmer";

$squadID = $search = "";

if (isset($_GET['squad'])) {
  $squadID = (int) $_GET['squad'];
}
if (isset($_GET['search'])) {
  $search = $_GET['search'];
}

$squads = $db->prepare("SELECT SquadName name, SquadID id FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, SquadName ASC");
$squads->execute([
  $tenant->getId()
]);

include BASE_PATH . "views/header.php";
include BASE_PATH . "controllers/attendance/attendanceMenu.php"; ?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance')) ?>">Attendance</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance/history')) ?>">History</a></li>
        <li class="breadcrumb-item active" aria-current="page">Members</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col">
        <h1>
          Attendance history by member
        </h1>
        <p class="lead mb-0">
          View up to 20 weeks of attendance history
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="visually-hidden" for="squad">Select a Squad</label>
      <select class="form-select" placeholder="Select a Squad" id="squad" name="squad">
        <option value="allSquads">Show All Squads</option>
        <?php while ($squad = $squads->fetch(PDO::FETCH_ASSOC)) { ?>
          <option value="<?= $squad['id'] ?>" <?php if ($squad['id'] == $squadID) { ?>selected<?php } ?>>
            <?= htmlspecialchars($squad['name']) ?>
          </option>
        <?php } ?>
      </select>
    </div>
    <div class="col-md-6 mb-3">
      <label class="visually-hidden" for="search">Search by Surname</label>
      <input class="form-control" placeholder="Surname" id="search" name="search" value="<?= htmlspecialchars($search) ?>">
    </div>
  </div>

  <div id="output">
    <div class="ajaxPlaceholder">
      <span class="h1 d-block">
        <i class="fa fa-spin fa-circle-o-notch" aria-hidden="true"></i><br>
        Loading Content
      </span>
      If content does not display, please turn on JavaScript
    </div>
  </div>

  <div id="ajax-data" data-page-url="<?= htmlspecialchars(autoUrl('attendance/history/members')) ?>" data-ajax-url="<?= htmlspecialchars(autoUrl('attendance/history/ajax/swimmers')) ?>"></div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/attendance/history/members.js');
$footer->render();
