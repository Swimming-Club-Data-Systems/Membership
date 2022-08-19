<?php

$selectedSquad = null;
if (isset($_GET['squad'])) {
  $selectedSquad = $_GET['squad'];
}

$db = app()->db;
$tenant = app()->tenant;

$squads = $db->prepare("SELECT SquadName `name`, SquadID id FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, `name` ASC");
$squads->execute([
  $tenant->getId()
]);
$squad = $squads->fetch(PDO::FETCH_ASSOC);

$dateToday = new DateTime('now', new DateTimeZone('Europe/London'));
$datePlusYear = new DateTime('+1 year', new DateTimeZone('Europe/London'));

// Get Venues
$getVenues = $db->prepare("SELECT VenueName, VenueID FROM sessionsVenues WHERE Tenant = ? ORDER BY VenueName ASC");
$getVenues->execute([
  $tenant->getId()
]);
$venue = $getVenues->fetch(PDO::FETCH_ASSOC);

// Get Squads
$getSquadsNew = $db->prepare("SELECT SquadName, SquadID FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, SquadName ASC");
$getSquadsNew->execute([
  $tenant->getId()
]);
$squadNew = $getSquadsNew->fetch(PDO::FETCH_ASSOC);

$fluidContainer = true;


/*$epoch = date(DATE_ATOM, mktime(0, 0, 0, 1, 1, 1970));
$displayUntil = date(strtotime());
echo $epoch . "<br>";
if ($displayUntil < $epoch) {
  $displayUntil = null;
  echo "TRUE";
}*/

include BASE_PATH . 'views/header.php';
include "attendanceMenu.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-fluid">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance')) ?>">Attendance</a></li>
        <li class="breadcrumb-item active" aria-current="page">Manage Sessions</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Manage Sessions
        </h1>
        <p class="lead mb-0">
          Add or end scheduled squad sessions.
        </p>
      </div>
      <div class="col text-end">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#add-session-modal">
          Add new session
        </button>
      </div>
    </div>
  </div>
</div>

<div id="ajax-info" data-page-url="<?= htmlspecialchars(autoUrl('attendance/sessions')) ?>" data-ajax-url="<?= htmlspecialchars(autoUrl('attendance/sessions/ajax/handler')) ?>" data-ajax-add-session-url="<?= htmlspecialchars(autoUrl('attendance/sessions/new')) ?>"></div>

<div class="container-fluid">
  <div class="row mb-3">
    <div class="col">
      <div class="card card-body h-100">
        <h2>Select a Squad to Manage its Sessions</h2>
        <form>
          <div class="mb-3">
            <label class="form-label" for="squad">Select Squad</label>
            <select class="form-select" name="squad" id="squad">
              <option value="" selected disabled>Choose a squad</option>
              <?php do { ?>
                <option value="<?= $squad['id'] ?>" <?php if ($selectedSquad == $squad['id']) { ?>selected<?php } ?>>
                  <?= htmlspecialchars($squad['name']) ?>
                </option>
              <?php } while ($squad = $squads->fetch(PDO::FETCH_ASSOC)); ?>
            </select>
          </div>
        </form>
        <p class="mb-0">
          Then select from the options below to either View Sessions or Add a New Session for the squad
        </p>
      </div>
    </div>
  </div>

  <div id="modalArea">
    <div id="output">
      <div class="ajaxPlaceholder"><strong>Session Manager will appear here</strong> <br>Select a squad first</div>
    </div>
  </div>
</div>

<div class="modal fade" id="add-session-modal" tabindex="-1" aria-labelledby="add-session-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="add-session-modal-label">Add a new session</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
        <?php if (!$venue) { ?>
          <div class="alert alert-warning">
            <p class="mb-0">
              <strong>You must add a venue before you can add a session</strong>
            </p>
            <p class="mb-0">
              <a href="<?= htmlspecialchars(autoUrl('attendance/venues/new')) ?>" class="alert-link">Add a new venue now</a>
            </p>
          </div>
        <?php } ?>

        <form id="new-session-form" class="needs-validation" novalidate>

          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AddSessionError'])) { ?>
            <div class="alert alert-danger">
              <p class="mb-0">
                <strong>An error occurred:</strong>
              </p>
              <p class="mb-0">
                <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['AddSessionError']) ?>
              </p>
            </div>
          <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['AddSessionError']);
          } ?>

          <div class="mb-3">
            <label class="form-label" for="session-name">Session Name</label>
            <input class="form-control add-session-form-reset-input" type="text" name="session-name" id="session-name" required placeholder="e.g. Swimming, Land Training, Diving, Water Polo" data-default-value="">
            <div class="invalid-feedback">
              You must provide a name for this session such as <em>Swimming</em>, <em>Land Training</em>, <em>Diving</em> or <em>Water Polo</em>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="session-venue">Session Venue</label>
            <select class="form-select add-session-form-reset-input" name="session-venue" id="session-venue" required data-default-value="">
              <option selected disabled value="">Select a Venue</option>
              <?php if ($venue) { ?>
                <?php do { ?>
                  <option value="<?= htmlspecialchars($venue['VenueID']) ?>"><?= htmlspecialchars($venue['VenueName']) ?></option>
                <?php } while ($venue = $getVenues->fetch(PDO::FETCH_ASSOC)); ?>
              <?php } ?>
            </select>
            <div class="invalid-feedback">
              You must select a venue for this session
            </div>
          </div>

          <div class="mb-3" id="recurrence-radios">
            <p class="mb-2">
              Recurrence
            </p>
            <div class="form-check">
              <input type="radio" id="recurring-session" name="recurring" class="form-check-input" value="recurring" checked required>
              <label class="form-check-label" for="recurring-session">Weekly until cancelled</label>
            </div>
            <div class="form-check">
              <input type="radio" id="recurring-one-off" name="recurring" class="form-check-input" value="one-off">
              <label class="form-check-label" for="recurring-one-off">One-off</label>
            </div>
          </div>

          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label class="form-label" for="session-date">Session Date</label>
                <input type="date" class="form-control add-session-form-reset-input" name="session-date" id="session-date" placeholder="<?= htmlspecialchars($dateToday->format('Y-m-d')) ?>" value="<?= htmlspecialchars($dateToday->format('Y-m-d')) ?>" required data-default-value="<?= htmlspecialchars($dateToday->format('Y-m-d')) ?>" min="<?= htmlspecialchars($dateToday->format('Y-m-d')) ?>">
                <div class="invalid-feedback">
                  You must provide a date for this session
                </div>
              </div>
            </div>

            <div class="col" id="show-until-container">
              <div class="mb-3">
                <label class="form-label" for="session-end-date">Show Until</label>
                <input type="date" aria-labelledby="session-end-date-help" class="form-control add-session-form-reset-input" name="session-end-date" id="session-end-date" placeholder="<?= htmlspecialchars($datePlusYear->format('Y-m-d')) ?>" value="<?= htmlspecialchars($datePlusYear->format('Y-m-d')) ?>" data-default-value="<?= htmlspecialchars($datePlusYear->format('Y-m-d')) ?>" min="<?= htmlspecialchars($dateToday->format('Y-m-d')) ?>">
                <div class="invalid-feedback">
                  You must provide a valid end date for this session
                </div>
                <small id="session-end-date-help" class="form-text text-muted">
                  When this session should st op recurring
                </small>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label class="form-label" for="session-start-time">Start Time</label>
                <input type="time" class="form-control add-session-form-reset-input" name="session-start-time" id="session-start-time" placeholder="0" value="18:00" required data-default-value="18:00">
                <small id="session-start-time-help" class="form-text text-muted">
                  Make sure to use 24 Hour Time
                </small>
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label class="form-label" for="session-end-time">End Time</label>
                <input type="time" class="form-control add-session-form-reset-input" name="session-end-time" id="session-end-time" placeholder="0" value="18:30" required data-default-value="18:30">
                <small id="session-end-time-help" class="form-text text-muted">
                  Make sure to use 24 Hour Time
                </small>
              </div>
            </div>
          </div>

          <?php if ($squadNew) { ?>

            <p class="mb-2">
              Select squads
            </p>
            <div class="row">
              <?php do { ?>
                <div class="col-6 col-md-4 col-lg-3 mb-2">
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input add-session-form-squad-checkboxes" id="squad-check-<?= htmlspecialchars($squadNew['SquadID']) ?>" name="squad-<?= htmlspecialchars($squadNew['SquadID']) ?>" value="1">
                    <label class="form-check-label" for="squad-check-<?= htmlspecialchars($squadNew['SquadID']) ?>"><?= htmlspecialchars($squadNew['SquadName']) ?></label>
                  </div>
                </div>
              <?php } while ($squadNew = $getSquadsNew->fetch(PDO::FETCH_ASSOC)); ?>
            </div>
            <p class="text-muted mt-n2 show-if-one-off d-none">
              <small>If you don't select any squads, we will automatically assume this session requires booking and is open to all members. We'll direct you to the edit session booking page if this is the case.</small>
            </p>

          <?php } else { ?>
            <p class="show-if-one-off d-none">
              We can't assign this session to any squads because there are no squads in the system. We will automatically assume this session requires booking and is open to all members. We'll direct you to the edit session booking page for this session when you press <em>Add Session</em>.
            </p>
          <?php } ?>

          <div class="mb-3 mb-0">
            <p class="mb-2">
              Attendance monitoring
            </p>
            <div class="form-check">
              <input type="radio" id="main-sequence-all" name="main-sequence" class="form-check-input" value="all" checked required>
              <label class="form-check-label" for="main-sequence-all">This session is for all squad members</label>
            </div>
            <div class="form-check">
              <input type="radio" id="main-sequence-some" name="main-sequence" class="form-check-input" value="some">
              <label class="form-check-label" for="main-sequence-some">This session is only for some squad members</label>
            </div>
          </div>

          <div class="d-none show-if-one-off mb-3 mb-0 mt-3">
            <p class="mb-2">
              Require booking for this session
            </p>
            <div class="form-check">
              <input type="radio" id="require-booking-no" name="require-booking" class="form-check-input" value="0" checked required>
              <label class="form-check-label" for="require-booking-no">Don't require booking</label>
            </div>
            <div class="form-check">
              <input type="radio" id="require-booking-yes" name="require-booking" class="form-check-input" value="1">
              <label class="form-check-label" for="require-booking-yes">Require booking</label>
            </div>
          </div>

          <?= \SCDS\CSRF::write() ?>

        </form>
      </div>
      <div class="modal-footer">
        <div class="row">
          <div class="col-auto text-end">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" form="new-session-form" class="btn btn-success">Add session</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->addJS("js/attendance/sessions.js");
$footer->useFluidContainer();
$footer->render();
