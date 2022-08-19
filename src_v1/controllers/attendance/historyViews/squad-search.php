<?php

$db = app()->db;
$tenant = app()->tenant;

$getSquad = $db->prepare("SELECT SquadName FROM `squads` WHERE `SquadID` = ? AND Tenant = ?");
$getSquad->execute([
  $id,
  $tenant->getId()
]);
$squad = $getSquad->fetch(PDO::FETCH_ASSOC);

if ($squad == null) {
  halt(404);
}

$pagetitle = htmlspecialchars($squad['SquadName']) . " Attendance Search";

$date = new DateTime('now', new DateTimeZone('Europe/London'));
$dateMinus4 = new DateTime('-4 weeks today', new DateTimeZone('Europe/London'));

include BASE_PATH . "views/header.php";
include BASE_PATH . "controllers/attendance/attendanceMenu.php"; ?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance')) ?>">Attendance</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance/history')) ?>">History</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance/history/squads')) ?>">Squads</a></li>
        <li class="breadcrumb-item active" aria-current="page">Search</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col">
        <h1>
          <?= htmlspecialchars($squad['SquadName']) ?>
        </h1>
        <p class="lead mb-0">
          Search attendance history
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="card mb-3">
    <div class="card-header">
      Search parameters
    </div>
    <div class="card-body">
      <form id="search-form" action="" class="needs-validation" novalidate>
        <div class="row">
          <div class="col">
            <div class="mb-3">
              <label class="form-label" for="from-date">From</label>
              <input class="form-control" type="date" name="from-date" id="from-date" value="<?= htmlspecialchars($dateMinus4->format('Y-m-d')) ?>" max="<?= htmlspecialchars($date->format('Y-m-d')) ?>" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="YYYY-MM-DD">
              <div class="invalid-feedback">
                Please enter a valid date
              </div>
            </div>
          </div>
          <div class="col">
            <div class="mb-3">
              <label class="form-label" for="to-date">Until</label>
              <input class="form-control" type="date" name="to-date" id="to-date" value="<?= htmlspecialchars($date->format('Y-m-d')) ?>" max="<?= htmlspecialchars($date->format('Y-m-d')) ?>" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="YYYY-MM-DD">
              <div class="invalid-feedback">
                Please enter a valid date
              </div>
            </div>
          </div>
        </div>

        <p class="mb-0">
          <button type="submit" class="btn btn-primary">Search</button>
        </p>
      </form>
    </div>
  </div>

  <div class="mb-3" id="result-box">
    <div class="card">
      <div class="card-body text-center">
        <i class="fa fa-arrow-up" aria-hidden="true"></i>
        <p class="mb-0">
          <strong>Search to view data</strong>
        </p>

        <p class="mb-0">
          Fill out the form above to pick a date range
        </p>

      </div>
    </div>
  </div>

  <div id="ajax-data" data-page-url="<?= htmlspecialchars(autoUrl('attendance/history/squads/' . $id . '/search')) ?>" data-ajax-url="<?= htmlspecialchars(autoUrl('attendance/history/squads/search')) ?>" data-squad-id="<?= htmlspecialchars($id) ?>"></div>

</div>

<script>
  const options = document.getElementById('ajax-data').dataset;

  let searchForm = document.getElementById('search-form');
  searchForm.addEventListener('submit', event => {
    event.preventDefault();
    let formData = new FormData(searchForm);
    formData.append('squad', options.squadId);
    console.log(formData);

    // AJAX Request
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        let json = JSON.parse(this.responseText);
        console.log(json);

        if (json.html) {
          document.getElementById('result-box').innerHTML = json.html;
        }
      }
    }
    xmlhttp.open('POST', options.ajaxUrl, true);
    xmlhttp.send(formData);
  });
</script>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
