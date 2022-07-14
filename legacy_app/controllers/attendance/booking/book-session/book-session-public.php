<?php

$db = app()->db;
$tenant = app()->tenant;

include 'my-members/my-members.php';
include 'all-members/all-members.php';

if (!isset($_GET['session']) && !isset($_GET['date'])) halt(404);

$date = null;
try {
  $date = new DateTime($_GET['date'], new DateTimeZone('Europe/London'));
} catch (Exception $e) {
  halt(404);
}

// Get session
$getSession = $db->prepare("SELECT `SessionID`, `SessionName`, `DisplayFrom`, `DisplayUntil`, `StartTime`, `EndTime`, `VenueName`, `Location`, `SessionDay`, `MaxPlaces`, `AllSquads`, `RegisterGenerated`, `BookingOpens`, `BookingFee` FROM `sessionsBookable` INNER JOIN `sessions` ON sessionsBookable.Session = sessions.SessionID INNER JOIN `sessionsVenues` ON `sessions`.`VenueID` = `sessionsVenues`.`VenueID` WHERE `sessionsBookable`.`Session` = ? AND `sessionsBookable`.`Date` = ? AND `sessions`.`Tenant` = ? AND DisplayFrom <= ? AND DisplayUntil >= ?");
$getSession->execute([
  $_GET['session'],
  $date->format('Y-m-d'),
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

$numFormatter = new NumberFormatter("en-GB", NumberFormatter::SPELLOUT);

// Number booked in
$getBookedCount = $db->prepare("SELECT COUNT(*) FROM `sessionsBookings` WHERE `Session` = ? AND `Date` = ?");
$getBookedCount->execute([
  $session['SessionID'],
  $date->format('Y-m-d'),
]);
$bookedCount = $getBookedCount->fetchColumn();

$sessionDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $date->format('Y-m-d') .  '-' . $session['StartTime'], new DateTimeZone('Europe/London'));
$startTime = new DateTime($session['StartTime'], new DateTimeZone('UTC'));
$endTime = new DateTime($session['EndTime'], new DateTimeZone('UTC'));
$duration = $startTime->diff($endTime);
$hours = (int) $duration->format('%h');
$mins = (int) $duration->format('%i');

$sessionDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $date->format('Y-m-d') .  '-' . $session['StartTime'], new DateTimeZone('Europe/London'));

$bookingCloses = clone $sessionDateTime;
$bookingCloses->modify('-15 minutes');

$now = new DateTime('now', new DateTimeZone('Europe/London'));

$bookingClosed = $now > $bookingCloses || bool($session['RegisterGenerated']);

$getCoaches = $db->prepare("SELECT Forename fn, Surname sn, coaches.Type code FROM coaches INNER JOIN users ON coaches.User = users.UserID WHERE coaches.Squad = ? ORDER BY coaches.Type ASC, Forename ASC, Surname ASC");

$getSessionSquads = $db->prepare("SELECT SquadName, ForAllMembers, SquadID FROM `sessionsSquads` INNER JOIN `squads` ON sessionsSquads.Squad = squads.SquadID WHERE sessionsSquads.Session = ? ORDER BY SquadFee DESC, SquadName ASC;");
$getSessionSquads->execute([
  $session['SessionID'],
]);
$squadNames = $getSessionSquads->fetchAll(PDO::FETCH_ASSOC);

$theTitle = 'Book ' . $session['SessionName'] . ' at ' . $startTime->format('H:i') . ' on ' . $date->format('j F Y') . ' - ' . $tenant->getName();
$theLink = app('request')->curl;
$bookingLoginLink = autoUrl('login?target=' . rawurlencode('/' . $tenant->getCodeId() . '/sessions/booking/book?session=' . $session['SessionID'] . '&date=' . $date->format('Y-m-d')));

$bookingOpensTime = null;
$bookingOpen = true;
if ($session['BookingOpens']) {
  try {
    $bookingOpensTime = new DateTime($session['BookingOpens'], new DateTimeZone('UTC'));
    $bookingOpensTime->setTimezone(new DateTimeZone('Europe/London'));
    if ($bookingOpensTime > $now) {
      $bookingOpen = false;
    }
  } catch (Exception $e) {
    // Ignore
  }
}

$pagetitle = 'Session Booking';
include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('timetable')) ?>">Timetable</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('timetable/booking')) ?>">Booking</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($date->format('Y-m-d')) ?>-S<?= htmlspecialchars($session['SessionID']) ?></li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          <?= htmlspecialchars($session['SessionName']) ?> on <?= htmlspecialchars($date->format('l j')) ?><sup><?= htmlspecialchars($date->format('S')) ?></sup> <?= htmlspecialchars($date->format('F Y')) ?>
        </h1>
        <p class="lead mb-0">
          <?php if ($session['MaxPlaces']) { ?>There are <?= htmlspecialchars($numFormatter->format($session['MaxPlaces'])) ?> places at this session<?php } else { ?>There are unlimited places at this session<?php } ?>
        </p>
        <div class="mb-3 d-lg-none"></div>
      </div>
      <div class="col text-lg-end">
        <a href="<?= htmlspecialchars($bookingLoginLink) ?>" class="btn btn-primary">
          Login to book<?php if (!$bookingOpen) { ?> once open<?php } ?>
        </a>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8 order-2 order-lg-1 mb-3">
      <p class="lead">
        <span class="place-numbers-places-booked-string uc-first"><?= htmlspecialchars(mb_ucfirst($numFormatter->format($bookedCount))) ?></span> <span id="place-numbers-booked-places-member-string"><?php if ($bookedCount == 1) { ?>member has<?php } else { ?>members have<?php } ?></span> booked onto this session. <?php if ($session['MaxPlaces']) { ?><span class="place-numbers-places-remaining-string uc-first"><?= htmlspecialchars(mb_ucfirst($numFormatter->format($session['MaxPlaces'] - $bookedCount))) ?></span> <span id="place-numbers-places-remaining-member-string"><?php if (($session['MaxPlaces'] - $bookedCount) == 1) { ?>place remains<?php } else { ?>places remain<?php } ?></span> available.<?php } ?>
      </p>

      <?php if ($bookingClosed) { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>Booking has closed for this session</strong>
          </p>
          <p class="mb-0">
            Booking closed automatically at <?= htmlspecialchars($bookingCloses->format('H:i, j F Y (T)')) ?>, 15 minutes prior to the published session start time of <?= htmlspecialchars($sessionDateTime->format('H:i T')) ?>.
          </p>
        </div>
      <?php } ?>

      <h2>Session Details</h2>
      <dl class="row mb-0">
        <dt class="col-sm-3">Date</dt>
        <dd class="col-sm-9"><?= htmlspecialchars($sessionDateTime->format('l j F Y')) ?></dd>

        <dt class="col-sm-3">Starts at</dt>
        <dd class="col-sm-9"><?= htmlspecialchars($startTime->format('H:i')) ?></dd>

        <dt class="col-sm-3">Ends at</dt>
        <dd class="col-sm-9"><?= htmlspecialchars($endTime->format('H:i')) ?></dd>

        <dt class="col-sm-3">Duration</dt>
        <dd class="col-sm-9"><?php if ($hours > 0) { ?><?= $hours ?> hour<?php if ($hours > 1) { ?>s<?php } ?> <?php } ?><?php if ($mins > 0) { ?><?= $mins ?> minute<?php if ($mins > 1) { ?>s<?php } ?><?php } ?></dd>

        <dt class="col-sm-3">Price per place</dt>
        <dd class="col-sm-9">&pound;<?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $session['BookingFee']))->withPointMovedLeft(2)->toScale(2)) ?></dd>

        <?php if ($bookingOpensTime) { ?>
          <dt class="col-sm-3">Booking opens at</dt>
          <dd class="col-sm-9"><?= htmlspecialchars($bookingOpensTime->format('H:i, j F Y')) ?></dd>
        <?php } ?>

        <?php if ($session['MaxPlaces']) { ?>
          <dt class="col-sm-3">Total places available</dt>
          <dd class="col-sm-9 place-numbers-max-places-int"><?= htmlspecialchars($session['MaxPlaces']) ?></dd>
        <?php } ?>

        <dt class="col-sm-3">Places booked</dt>
        <dd class="col-sm-9 place-numbers-places-booked-int"><?= htmlspecialchars($bookedCount) ?></dd>

        <?php if ($session['MaxPlaces']) { ?>
          <dt class="col-sm-3">Places remaining</dt>
          <dd class="col-sm-9 place-numbers-places-remaining-int"><?= htmlspecialchars(($session['MaxPlaces'] - $bookedCount)) ?></dd>
        <?php } ?>

        <?php for ($i = 0; $i < sizeof($squadNames); $i++) {
          $getCoaches->execute([
            $squadNames[$i]['SquadID'],
          ]);
          $coaches = $getCoaches->fetchAll(PDO::FETCH_ASSOC);
        ?>
          <dt class="col-sm-3"><?= htmlspecialchars($squadNames[$i]['SquadName']) ?> Coach<?php if (sizeof($coaches) > 0) { ?>es<?php } ?></dt>
          <dd class="col-sm-9">
            <ul class="list-unstyled mb-0">
              <?php for ($y = 0; $y < sizeof($coaches); $y++) { ?>
                <li><strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($coaches[$y]['fn'], $coaches[$y]['sn'])) ?></strong>, <?= htmlspecialchars(coachTypeDescription($coaches[$y]['code'])) ?></li>
              <?php } ?>
              <?php if (sizeof($coaches) == 0) { ?>
                <li>None assigned</li>
              <?php } ?>
            </ul>
          </dd>
        <?php } ?>

        <dt class="col-sm-3">Location</dt>
        <dd class="col-sm-9"><?= htmlspecialchars($session['Location']) ?></dd>

        <dt class="col-sm-3">Session Unique ID</dt>
        <dd class="col-sm-9"><?= htmlspecialchars($date->format('Y-m-d')) ?>-S<?= htmlspecialchars($session['SessionID']) ?></dd>
      </dl>

      <h2>Book a place</h2>
      <?php if ($bookingOpen) { ?>
        <p class="lead">
          To book a place, please log into your <?= htmlspecialchars($tenant->getName()) ?> account.
        </p>
        <p>
          <a href="<?= htmlspecialchars($bookingLoginLink) ?>" class="btn btn-primary">
            Login
          </a>
        </p>
      <?php } else if ($bookingOpensTime) { ?>
        <p class="lead">
          Booking for this session will open at <?= htmlspecialchars($bookingOpensTime->format('H:i T, l j F Y')) ?>
        </p>

        <p>
          Come back to this page then and log in with your club account to book a space.
        </p>
      <?php } ?>

    </div>
    <div class="col order-1 order-lg-2">
      <div class="d-block d-lg-none">

      </div>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
