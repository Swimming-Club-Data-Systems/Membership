<?php

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

if (!$user->hasPermission('Admin') && !$user->hasPermission('Coach')) {
  halt(404);
}

if (!isset($_GET['session']) && !isset($_GET['date'])) halt(404);

$date = null;
try {
  $date = new DateTime($_GET['date'], new DateTimeZone('Europe/London'));
} catch (Exception $e) {
  halt(404);
}

// Get session
$getSession = $db->prepare("SELECT `SessionID`, `SessionName`, `DisplayFrom`, `DisplayUntil`, `StartTime`, `EndTime`, `VenueName`, `Location`, `SessionDay` FROM `sessions` INNER JOIN `sessionsVenues` ON `sessions`.`VenueID` = `sessionsVenues`.`VenueID` WHERE `sessions`.`SessionID` = ? AND `sessions`.`Tenant` = ? AND DisplayFrom <= ? AND DisplayUntil >= ?");
$getSession->execute([
  $_GET['session'],
  $tenant->getId(),
  $date->format('Y-m-d'),
  $date->format('Y-m-d'),
]);

$session = $getSession->fetch(PDO::FETCH_ASSOC);

if (!$session) {
  halt(404);
}

// Validate session happens on this day
$dayOfWeek = $date->format('w');

if ($session['SessionDay'] != $dayOfWeek) {
  halt(404);
}

$sessionDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $date->format('Y-m-d') .  '-' . $session['StartTime'], new DateTimeZone('Europe/London'));
$bookingCloses = clone $sessionDateTime;
$bookingCloses->modify('-15 minutes');

$now = new DateTime('now', new DateTimeZone('Europe/London'));

$bookingClosed = $now > $bookingCloses;

$pagetitle = 'Session Booking';
include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('timetable')) ?>">Timetable</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('timetable/booking')) ?>">Booking</a></li>
        <li class="breadcrumb-item active" aria-current="page">Require Booking</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col">
        <h1>
          Require booking for <?= htmlspecialchars($session['SessionName']) ?> on <?= htmlspecialchars($date->format('j F Y')) ?>
        </h1>
        <p class="lead mb-0">
          Book numbers limited or pay as you go sessions
        </p>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <form class="needs-validation" method="post" action="<?= htmlspecialchars(autoUrl('sessions/booking/require-booking')) ?>" novalidate>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RequireBookingError'])) { ?>
          <div class="alert alert-warning">
            <p class="mb-0">
              <strong>Error</strong>
            </p>
            <p class="mb-0">
              <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['RequireBookingError']) ?>
            </p>
          </div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['RequireBookingError']);
        } ?>

        <?php if ($bookingClosed) { ?>
          <div class="alert alert-warning">
            <p class="mb-0">
              <strong>Booking has closed for this session</strong>
            </p>
            <p class="mb-0">
              Registers have been automatically generated and you can no longer edit booking settings for the session.
            </p>
          </div>
        <?php } ?>

        <div class="mb-3">
          <label class="form-label" for="session-text-description">Session</label>
          <input type="text" id="session-text-description" name="session-text-description" readonly class="form-control" value="<?= htmlspecialchars('#' . $session['SessionID'] . ' - ' . $session['SessionName']) ?>" <?php if ($bookingClosed) { ?>disabled<?php } ?>>
        </div>

        <input type="hidden" name="session" value="<?= htmlspecialchars($session['SessionID']) ?>">

        <div class="mb-3">
          <label class="form-label" for="date">Date</label>
          <input type="date" id="date" name="date" readonly class="form-control" value="<?= htmlspecialchars($date->format('Y-m-d')) ?>" <?php if ($bookingClosed) { ?>disabled<?php } ?>>
        </div>

        <div class="mb-3" id="number-limit">
          <div class="form-check">
            <input type="radio" id="unlimited-numbers" name="number-limit" class="form-check-input" value="0" required <?php if ($bookingClosed) { ?>disabled<?php } ?> checked>
            <label class="form-check-label" for="unlimited-numbers">Unlimited numbers</label>
          </div>
          <div class="form-check">
            <input type="radio" id="limited-numbers" name="number-limit" class="form-check-input" value="1" <?php if ($bookingClosed) { ?>disabled<?php } ?>>
            <label class="form-check-label" for="limited-numbers">Limited numbers</label>
          </div>
        </div>

        <div class="d-none" id="max-places-container">
          <div class="mb-3">
            <label class="form-label" for="max-count">Maximum places</label>
            <input type="number" id="max-count" name="max-count" min="1" step="1" class="form-control" value="" <?php if ($bookingClosed) { ?>disabled<?php } ?>>
            <div class="invalid-feedback">
              Please provide a positive integer
            </div>
          </div>

          <p>
            If you later reduce the limit on a session to a number lower than the number of bookings, then those who booked a place first will keep their places and the most recent bookings being removed down to the new limit.
          </p>
        </div>

        <div class="mb-3">
          <div class="form-check">
            <input type="radio" id="open-to-squads" name="open-to" class="form-check-input" value="0" required <?php if ($bookingClosed) { ?>disabled<?php } ?> checked>
            <label class="form-check-label" for="open-to-squads">Open to this session's scheduled squads</label>
          </div>
          <div class="form-check">
            <input type="radio" id="open-to-all" name="open-to" class="form-check-input" value="1" <?php if ($bookingClosed) { ?>disabled<?php } ?>>
            <label class="form-check-label" for="open-to-all">Open to all members</label>
          </div>
        </div>

        <p>
          We will generate a register for this session based on bookings rather than squad membership.
        </p>

        <div class="mb-3" id="open-bookings">
          <div class="form-check">
            <input type="radio" id="open-bookings-now" name="open-bookings" class="form-check-input" value="0" required <?php if ($bookingClosed) { ?>disabled<?php } ?> checked>
            <label class="form-check-label" for="open-bookings-now">Opening bookings immediately</label>
          </div>
          <div class="form-check">
            <input type="radio" id="open-bookings-later" name="open-bookings" class="form-check-input" value="1" <?php if ($bookingClosed) { ?>disabled<?php } ?>>
            <label class="form-check-label" for="open-bookings-later">Open bookings later</label>
          </div>
        </div>

        <div class="d-none" id="open-bookings-at-container">
          <div class="row g-2">
            <div class="col">
              <div class="mb-3">
                <label class="form-label" for="open-booking-at-date">Opens date</label>
                <input type="date" id="open-booking-at-date" name="open-booking-at-date" min="<?= htmlspecialchars($now->format("Y-m-d")) ?>" max="<?= htmlspecialchars($date->format("Y-m-d")) ?>" class="form-control" value="<?= htmlspecialchars($now->format("Y-m-d")) ?>" <?php if ($bookingClosed) { ?>disabled<?php } ?>>
                <div class="invalid-feedback">
                  Please provide a date
                </div>
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label class="form-label" for="open-booking-at-time">Opens time</label>
                <input type="time" id="open-booking-at-time" name="open-booking-at-time" class="form-control" <?php if ($bookingClosed) { ?>disabled<?php } ?> value="<?= htmlspecialchars($now->format("H:i")) ?>">
                <div class="invalid-feedback">
                  Please provide a time (24 hour format)
                </div>
              </div>
            </div>
          </div>

          <p>
            As a coach or administrator, you can add members to this session ahead of booking opening.
          </p>
        </div>

        <div class="mb-3" id="booking-fees">
          <div class="form-check">
            <input type="radio" id="booking-fees-no" name="booking-fees" class="form-check-input" value="0" required <?php if ($bookingClosed) { ?>disabled<?php } ?> checked>
            <label class="form-check-label" for="booking-fees-no">Session is free</label>
          </div>
          <div class="form-check">
            <input type="radio" id="booking-fees-yes" name="booking-fees" class="form-check-input" value="1" <?php if ($bookingClosed) { ?>disabled<?php } ?>>
            <label class="form-check-label" for="booking-fees-yes">Charge a fee</label>
          </div>
        </div>

        <div class="d-none" id="booking-fees-container">
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label class="form-label" for="booking-fees-amount">Booking fee</label>
                <div class="input-group">
                  <span class="input-group-text">&pound;</span>
                  <input type="num" id="booking-fees-amount" name="booking-fees-amount" min="0" max="50" step="0.01" class="form-control" <?php if ($bookingClosed) { ?>disabled<?php } ?> placeholder="0.00">
                  <div class="invalid-feedback">
                    Please provide a session fee
                  </div>
                </div>
              </div>
            </div>
          </div>

          <p>
            All members who book a space on this session will be charged this amount.
          </p>
        </div>

        <p>
          Booking <?php if ($bookingClosed) { ?>closed<?php } else { ?>will close<?php } ?> automatically at <?= htmlspecialchars($bookingCloses->format('H:i, j F Y (T)')) ?>, 15 minutes prior to the session start time of <?= htmlspecialchars($sessionDateTime->format('H:i T')) ?>.
        </p>

        <p>
          <button type="submit" class="btn btn-primary" <?php if ($bookingClosed) { ?>disabled<?php } ?>>
            Require Booking
          </button>
        </p>

      </form>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/attendance/booking/require-booking.js');
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
