<?php

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

if (!$user->hasPermission('Admin') && !$user->hasPermission('Committee') && !$user->hasPermission('Coach')) {
  halt(404);
}

$dateToday = new DateTime('now', new DateTimeZone('Europe/London'));
$datePlusYear = new DateTime('+1 year', new DateTimeZone('Europe/London'));

// Get Venues
$getVenues = $db->prepare("SELECT VenueName, VenueID FROM sessionsVenues WHERE Tenant = ? ORDER BY VenueName ASC");
$getVenues->execute([
  $tenant->getId()
]);
$venue = $getVenues->fetch(PDO::FETCH_ASSOC);

// Get Squads
$getSquads = $db->prepare("SELECT SquadName, SquadID FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, SquadName ASC");
$getSquads->execute([
  $tenant->getId()
]);
$squad = $getSquads->fetch(PDO::FETCH_ASSOC);

$pagetitle = 'Add New Session';

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance')) ?>">Attendance</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance/sessions')) ?>">Manage Sessions</a></li>
        <li class="breadcrumb-item active" aria-current="page">New One Off</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Add a new session
        </h1>
        <p class="lead mb-0">
          Add a session to the timetable
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

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

      <form method="post" class="needs-validation" novalidate>

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
          <input class="form-control" type="text" name="session-name" id="session-name" required placeholder="e.g. Swimming, Land Training, Diving, Water Polo">
          <div class="invalid-feedback">
            You must provide a name for this session such as <em>Swimming</em>, <em>Land Training</em>, <em>Diving</em> or <em>Water Polo</em>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="session-venue">Session Venue</label>
          <select class="form-select" name="session-venue" id="session-venue" required>
            <option selected value="">Select a Venue</option>
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
            <input type="radio" id="recurring-one-off" name="recurring" class="form-check-input" checked required value="one-off">
            <label class="form-check-label" for="recurring-one-off">One-off</label>
          </div>
          <div class="form-check">
            <input type="radio" id="recurring-session" name="recurring" class="form-check-input" value="recurring">
            <label class="form-check-label" for="recurring-session">Weekly until cancelled</label>
          </div>
        </div>

        <div class="row">
          <div class="col">
            <div class="mb-3">
              <label class="form-label" for="session-date">Session Date</label>
              <input type="date" class="form-control" name="session-date" id="session-date" placeholder="<?= htmlspecialchars($dateToday->format('Y-m-d')) ?>" value="<?= htmlspecialchars($dateToday->format('Y-m-d')) ?>" required>
              <div class="invalid-feedback">
                You must provide a date for this session
              </div>
            </div>
          </div>

          <div class="col d-none" id="show-until-container">
            <div class="mb-3">
              <label class="form-label" for="session-end-date">Show Until</label>
              <input type="date" aria-labelledby="session-end-date-help" class="form-control" name="session-end-date" id="session-end-date" placeholder="<?= htmlspecialchars($datePlusYear->format('Y-m-d')) ?>" value="<?= htmlspecialchars($datePlusYear->format('Y-m-d')) ?>">
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
              <input type="time" class="form-control" name="session-start-time" id="session-start-time" placeholder="0" value="18:00" required>
              <small id="session-start-time-help" class="form-text text-muted">
                Make sure to use 24 Hour Time
              </small>
            </div>
          </div>
          <div class="col">
            <div class="mb-3">
              <label class="form-label" for="session-end-time">End Time</label>
              <input type="time" class="form-control" name="session-end-time" id="session-end-time" placeholder="0" value="18:30" required>
              <small id="session-end-time-help" class="form-text text-muted">
                Make sure to use 24 Hour Time
              </small>
            </div>
          </div>
        </div>

        <?php if ($squad) { ?>

          <p class="mb-2">
            Select squads
          </p>
          <div class="row">
            <?php do { ?>
              <div class="col-6 col-md-4 col-lg-3 mb-2">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="squad-check-<?= htmlspecialchars($squad['SquadID']) ?>" name="squad-<?= htmlspecialchars($squad['SquadID']) ?>" value="1">
                  <label class="form-check-label" for="squad-check-<?= htmlspecialchars($squad['SquadID']) ?>"><?= htmlspecialchars($squad['SquadName']) ?></label>
                </div>
              </div>
            <?php } while ($squad = $getSquads->fetch(PDO::FETCH_ASSOC)); ?>
          </div>
          <p class="text-muted mt-n2 show-if-one-off">
            <small>If you don't select any squads, we will automatically assume this session requires booking and is open to all members. We'll direct you to the edit session booking page if this is the case.</small>
          </p>

        <?php } else { ?>
          <p class="show-if-one-off">
            We can't assign this session to any squads because there are no squads in the system. We will automatically assume this session requires booking and is open to all members. We'll direct you to the edit session booking page for this session when you press <em>Add Session</em>.
          </p>
        <?php } ?>

        <?= \SCDS\CSRF::write() ?>

        <div class="d-md-flex py-3">
          <button type="submit" class="btn btn-primary me-sm-auto">
            Add Session
          </button>
          <div class="show-if-one-off">
            <div class="mb-3 d-block d-md-none"></div>
            <button type="submit" class="btn btn-dark-l btn-outline-light-d" name="go-to-booking-settings" value="1">
              Add Session, Require Booking
            </button>
          </div>
        </div>

      </form>

    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->addJS("js/attendance/add-session.js");
$footer->render();
