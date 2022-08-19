<?php

$db = app()->db;
$tenant = app()->tenant;

$venues = $db->prepare("SELECT `VenueID`, `VenueName`, `Location` FROM sessionsVenues WHERE Tenant = ? ORDER BY VenueName ASC");
$venues->execute([
  $tenant->getId()
]);

$pagetitle = "Venues";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance')) ?>">Attendance</a></li>
        <li class="breadcrumb-item active" aria-current="page">Venues</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg">
        <h1>
          Venues
        </h1>
        <p class="lead mb-0">
          Venues used for sessions at <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>
        </p>
        <div class="mb-3 d-lg-none"></div>
      </div>
      <div class="col-auto">
        <a href="<?= autoUrl("attendance/venues/new") ?>" class="btn btn-success">
          New Venue
        </a>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">

      <?php
      if ($row = $venues->fetch(PDO::FETCH_ASSOC)) {
        do {
          $address = explode(',', $row['Location']) ?>
          <div class="card card-body mb-3">
            <h2><?= htmlspecialchars($row['VenueName']) ?></h2>
            <ul class="list-unstyled">
              <?php for ($i = 0; $i < sizeof($address); $i++) {
                $strong = $strong_end = "";
                if ($i == 0) {
                  $strong = "<strong>";
                  $strong_end = "</strong>";
                } ?>
                <li><?= $strong ?><?= htmlspecialchars(trim($address[$i])) ?><?= $strong_end ?></li>
              <?php } ?>
            </ul>
            <p class="mb-0">
              <a href="<?= autoUrl("attendance/venues/" . $row['VenueID']) ?>" class="btn btn-primary">
                Edit
              </a>
          </div>

        <?php } while ($row = $venues->fetch(PDO::FETCH_ASSOC)); ?>
      <?php } else { ?>
        <div class="alert alert-warning mb-0">
          <p class="mb-0">
            You need to populate the Membership System with venues before you can
            add sessions for squad registers.
          </p>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
