<?php

$db = app()->db;
$tenant = app()->tenant;

$now = new DateTime('now', new DateTimeZone('Europe/London'));
$startWeek = new DateTime('monday -1 week', new DateTimeZone('UTC'));
$now->setTimezone(new DateTimeZone('UTC'));

$closedBookingsTime = new DateTime('+15 minutes', new DateTimeZone('Europe/London'));

$day = null;

if ($now->format('l') == 'Monday') {
  $startWeek = clone $now;
}

if (isset($_GET['year']) && isset($_GET['week'])) {
  if (!$startWeek->setISODate((int) $_GET['year'], (int) $_GET['week'], 1)) {
    halt(404);
  }
}

$endWeek = clone $startWeek;

$endWeek->add(new DateInterval('P6D'));

$week = $startWeek->format('W');
$day = $startWeek->format('d');
$month = $startWeek->format('m');
$year = $startWeek->format('Y');

$allSessions = [];

$sessions = $pageSquad = null;
if (isset($_GET['squad'])) {

  // Validate squad
  $getSquad = $db->prepare("SELECT SquadName, SquadID FROM squads WHERE squads.Tenant = ? AND squads.SquadID = ?");
  $getSquad->execute([
    $tenant->getId(),
    $_GET['squad'],
  ]);

  $pageSquad = $getSquad->fetch(PDO::FETCH_ASSOC);

  if (!$pageSquad) {
    halt(404);
  }
}

for ($i = 0; $i < 7; $i++) {
  $date = clone $startWeek;
  $date->add(new DateInterval('P' . $i . 'D'));

  if (isset($_GET['squad'])) {
    $sessions = $db->prepare("SELECT * FROM ((`sessions` INNER JOIN sessionsSquads ON `sessions`.`SessionID` = sessionsSquads.Session) INNER JOIN sessionsVenues ON sessionsVenues.VenueID = sessions.VenueID) WHERE sessionsSquads.Squad = :squad AND sessions.Tenant = :tenant AND DisplayFrom <= :today AND DisplayUntil >= :today AND SessionDay = :dayNum ORDER BY SessionDay ASC, StartTime ASC, EndTime ASC;");
    $sessions->execute([
      'squad' => (int) $_GET['squad'],
      'tenant' => $tenant->getId(),
      'today' => $date->format('Y-m-d'),
      'dayNum' => $date->format('w'),
    ]);
  } else {
    $sessions = $db->prepare("SELECT * FROM (`sessions` INNER JOIN sessionsVenues ON sessionsVenues.VenueID = sessions.VenueID) WHERE sessions.Tenant = :tenant AND DisplayFrom <= :today AND DisplayUntil >= :today AND SessionDay = :dayNum ORDER BY SessionDay ASC, StartTime ASC, EndTime ASC;");
    $sessions->execute([
      'tenant' => $tenant->getId(),
      'today' => $date->format('Y-m-d'),
      'dayNum' => $date->format('w'),
    ]);
  }

  $daySessions = $sessions->fetchAll(PDO::FETCH_ASSOC);
  foreach ($daySessions as $session) {
    $allSessions[] = $session;
  }
}
$getSessionSquads = $db->prepare("SELECT SquadName, ForAllMembers, SquadID FROM `sessionsSquads` INNER JOIN `squads` ON sessionsSquads.Squad = squads.SquadID WHERE sessionsSquads.Session = ? ORDER BY SquadFee DESC, SquadName ASC;");

$dayNum = (int) $now->format('N') % 7;
$sessionToday = false;

$otherDays = $sundays = [];
foreach ($allSessions as $session) {
  if (($startWeek <= $now && $now <= $endWeek) && !$sessionToday && $dayNum == $session['SessionDay']) {
    $sessionToday = true;
  }
  if ($session['SessionDay'] == 0) {
    $sundays[] = $session;
  } else {
    $otherDays[] = $session;
  }
}
foreach ($sundays as $session) {
  $otherDays[] = $session;
}
$sessions = $otherDays;

$showAdmin = false;
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) {
  if (app()->user->hasPermissions(['Admin', 'Coach'])) $showAdmin = true;
}

$getBookingRequired = $db->prepare("SELECT COUNT(*) FROM `sessionsBookable` INNER JOIN `sessions` ON `sessions`.`SessionID` = `sessionsBookable`.`Session` WHERE `sessionsBookable`.`Session` = ? AND `sessionsBookable`.`Date` = ? AND `sessions`.`Tenant` = ?");

$getCoaches = $db->prepare("SELECT Forename fn, Surname sn, coaches.Type code FROM coaches INNER JOIN users ON coaches.User = users.UserID WHERE coaches.Squad = ? ORDER BY coaches.Type ASC, Forename ASC, Surname ASC");

$days = [
  0 => false,
  1 => false,
  2 => false,
  3 => false,
  4 => false,
  5 => false,
  6 => false,
];

$pageHead = [
  'body' => [
    'data-bs-spy="scroll"',
    'data-bs-target="#member-page-menu"'
  ]
];
$pagetitle = "Timetable";

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb" class="d-print-none">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Timetable</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Timetable - Week <?= htmlspecialchars($startWeek->format('W')) ?> / <?= htmlspecialchars($startWeek->format('o')) ?>
        </h1>
        <p class="lead mb-0">
          <?php if ($pageSquad) { ?><?= htmlspecialchars($pageSquad['SquadName']) ?><?php } else { ?>All squads<?php } ?>
        </p>
      </div>
      <div class="col d-print-none">
        <div class="alert alert-warning mb-0">
          <p class="mb-0"><strong>Please note:</strong> This system does not currently indicate whether or not a session is cancelled.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <p>Showing sessions for the week beginning <strong><?= htmlspecialchars($startWeek->format('l j F Y')) ?></strong>.</p>

  <?php $weeks = clone $startWeek; ?>
  <?php $weeks->sub(new DateInterval('P7D')); ?>
  <div class="row my-3">
    <div class="col-auto">
      <nav aria-label="Page navigation" class="d-print-none">
        <ul class="pagination my-0">
          <li class="page-item">
            <a class="page-link" href="<?= autoUrl("timetable?year=" . $weeks->format('o') . "&week=" . $weeks->format('W')) ?>">
              Previous Week
            </a>
          </li>
          <?php $weeks->add(new DateInterval('P7D')); ?>
          <li class="page-item">
            <a class="page-link" href="<?= autoUrl("timetable?year=" . $weeks->format('o') . "&week=" . $weeks->format('W')) ?>">
              Week <?= (int) $weeks->format('W') ?> / <?= $weeks->format('o') ?>
            </a>
          </li>
          <?php $weeks->add(new DateInterval('P7D')); ?>
          <li class="page-item">
            <a class="page-link" href="<?= autoUrl("timetable?year=" . $weeks->format('o') . "&week=" . $weeks->format('W')) ?>">
              Next Week
            </a>
          </li>
        </ul>
      </nav>
      <div class="mb-3 d-md-none"></div>
    </div>
    <div class="col-md">
      <form action="<?= htmlspecialchars(autoUrl('timetable/jump-to-week')) ?>" method="post" class="needs-validation d-print-none" novalidate>
        <?php if ($pageSquad) { ?>
          <input type="hidden" name="squad" value="<?= htmlspecialchars($pageSquad['SquadID']) ?>">
        <?php } ?>
        <div class="input-group">
          <input type="date" class="form-control" value="<?= htmlspecialchars($startWeek->format('Y-m-d')) ?>" aria-label="Find a week" aria-describedby="go-to-week" name="go-to-week-date" id="go-to-week-date">
          <button class="btn btn-primary" type="submit" id="go-to-week">Go to week</button>
        </div>
      </form>
    </div>
  </div>

  <?php if ($sessionToday) { ?>
    <p class="d-print-none"><a href="#day-<?= $dayNum ?>">Jump to today</a></p>
  <?php } ?>

  <div class="row">
    <div class="col-lg-8 order-2 order-lg-1">
      <?php $currentDay = null; ?>

      <?php if (sizeof($sessions) > 0) { ?>
        <div class="list-group">
          <?php foreach ($sessions as $session) {
            $getSessionSquads->execute([
              $session['SessionID'],
            ]);
            $squadNames = $getSessionSquads->fetchAll(PDO::FETCH_ASSOC);
          ?>
            <?php if ($currentDay != $session['SessionDay']) {
              $currentDay = $session['SessionDay'];
              $day = clone $startWeek;
              $day->add(new DateInterval('P' . ($currentDay + 6) % 7 . 'D'));
              $days[(int) $day->format('N') % 7] = true; ?>
              <div class="list-group-item bg-tenant-brand tenant-colour" id="day-<?= htmlspecialchars($day->format('N') % 7) ?>">
                <h2 class="mb-0"><?= htmlspecialchars($day->format('l')) ?></h2>
                <p class="lead mb-0"><?= htmlspecialchars($day->format('j F Y')) ?></p>
              </div>
            <?php } ?>

            <?php
            $sessionDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $day->format('Y-m-d') .  '-' . $session['StartTime'], new DateTimeZone('Europe/London'));
            $startTime = new DateTime($session['StartTime'], new DateTimeZone('UTC'));
            $endTime = new DateTime($session['EndTime'], new DateTimeZone('UTC'));
            ?>

            <div class="list-group-item" id="<?= htmlspecialchars('session-unique-id-' . $sessionDateTime->format('Y-m-d') . '-S' . $session['SessionID']) ?>">
              <h3 class="mb-0"><?php if (sizeof($squadNames) > 0) { ?><?php for ($i = 0; $i < sizeof($squadNames); $i++) { ?><?php if ($i > 0) { ?>, <?php } ?><?= htmlspecialchars($squadNames[$i]['SquadName']) ?><?php } ?><?php } else { ?>Any Member<?php } ?></h3>
              <p class="h3"><small><?= htmlspecialchars($session['SessionName']) ?>, <?= htmlspecialchars($session['VenueName']) ?></small></p>

              <dl class="row mb-0">
                <dt class="col-sm-3">Starts at</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($startTime->format('H:i')) ?></dd>

                <dt class="col-sm-3">Ends at</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($endTime->format('H:i')) ?></dd>

                <?php
                $duration = $startTime->diff($endTime);
                $hours = (int) $duration->format('%h');
                $mins = (int) $duration->format('%i');
                ?>

                <dt class="col-sm-3">Duration</dt>
                <dd class="col-sm-9"><?php if ($hours > 0) { ?><?= $hours ?> hour<?php if ($hours > 1) { ?>s<?php } ?> <?php } ?><?php if ($mins > 0) { ?><?= $mins ?> minute<?php if ($mins > 1) { ?>s<?php } ?><?php } ?></dd>

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

                <?php
                $futureSession = false;
                if ($sessionDateTime > $closedBookingsTime) {
                  $futureSession = true;
                }
                // Work out if booking required
                $getBookingRequired->execute([
                  $session['SessionID'],
                  $sessionDateTime->format('Y-m-d'),
                  $tenant->getId(),
                ]);
                $bookingRequired = $getBookingRequired->fetchColumn() > 0;
                ?>
                <dt class="col-sm-3">Booking</dt>
                <dd class="col-sm-9">
                  <?php if ($bookingRequired && $futureSession) { ?>
                    <span class="d-block mb-2">Booking is required for this session</span>
                    <a href="<?= htmlspecialchars(autoUrl('timetable/booking/book?session=' . urlencode($session['SessionID']) . '&date=' . urlencode($sessionDateTime->format('Y-m-d')))) ?>" class="btn btn-success d-print-none">Book a place<?php if ($showAdmin) { ?> or view/edit details<?php } ?></a>
                  <?php } else if ($showAdmin && $futureSession) { ?>
                    <span class="d-block mb-2">Booking is not currently required for this session</span>
                    <a href="<?= htmlspecialchars(autoUrl('timetable/booking/book?session=' . urlencode($session['SessionID']) . '&date=' . urlencode($sessionDateTime->format('Y-m-d')))) ?>" class="btn btn-primary d-print-none">Require pre-booking</a>
                  <?php } else if ($showAdmin && $bookingRequired && !$futureSession) { ?>
                    <span class="d-block mb-2">Booking was required for this session</span>
                    <a href="<?= htmlspecialchars(autoUrl('timetable/booking/book?session=' . urlencode($session['SessionID']) . '&date=' . urlencode($sessionDateTime->format('Y-m-d')))) ?>" class="btn btn-primary d-print-none">View booking info</a>
                  <?php } else if (!$futureSession) { ?>
                    <span class="d-block">Booking was not required</span>
                  <?php } else { ?>
                    <span class="d-block">Booking is not required</span>
                  <?php } ?>
                </dd>

                <?php
                // IN FUTURE: LINK TO A LOCATION PAGE
                // GEOCODE AND USE A MAP
                ?>
                <dt class="col-sm-3">Location</dt>
                <dd class="col-sm-9 mb-0"><?= htmlspecialchars($session['Location']) ?></dd>
              </dl>

              <?php for ($i = 0; $i < sizeof($squadNames); $i++) { ?>
                <?php if (!bool($squadNames[$i]['ForAllMembers'])) { ?>
                  <div class="alert alert-warning mt-3 mb-0">
                    <p class="mb-0"><strong>This session is not for all swimmers in <?= htmlspecialchars($squadNames[$i]['SquadName']) ?></strong></p>
                    <p class="mb-0">Your coach will tell you if you are to attend this session</p>
                  </div>
                <?php } ?>
              <?php } ?>
            </div>
          <?php } ?>
        </div>
      <?php } else { ?>
        <div class="alert alert-warning">
          <strong>There are no sessions to show this week.</strong>
        </div>
      <?php } ?>

      <?php $weeks = clone $startWeek; ?>
      <?php $weeks->sub(new DateInterval('P7D')); ?>
      <nav aria-label="Page navigation example" class="d-print-none">
        <ul class="pagination">
          <li class="page-item">
            <a class="page-link" href="<?= autoUrl("timetable?year=" . $weeks->format('o') . "&week=" . $weeks->format('W')) ?>">
              Previous Week
            </a>
          </li>
          <?php $weeks->add(new DateInterval('P7D')); ?>
          <li class="page-item">
            <a class="page-link" href="<?= autoUrl("timetable?year=" . $weeks->format('o') . "&week=" . $weeks->format('W')) ?>">
              Week <?= (int) $weeks->format('W') ?> / <?= $weeks->format('o') ?>
            </a>
          </li>
          <?php $weeks->add(new DateInterval('P7D')); ?>
          <li class="page-item">
            <a class="page-link" href="<?= autoUrl("timetable?year=" . $weeks->format('o') . "&week=" . $weeks->format('W')) ?>">
              Next Week
            </a>
          </li>
        </ul>
      </nav>
    </div>
    <div class="col order-1 order-lg-2 d-print-none">
      <div class="position-sticky top-3 card mb-3">
        <div class="card-header">
          Jump to day
        </div>
        <div class="list-group list-group-flush" id="member-page-menu">
          <a href="#day-1" <?php if (!$days[1]) { ?>tabindex="-1" aria-disabled="true" <?php } ?> class="list-group-item list-group-item-action <?php if (!$days[1]) { ?>disabled<?php } ?>">
            Monday
          </a>
          <a href="#day-2" <?php if (!$days[2]) { ?>tabindex="-1" aria-disabled="true" <?php } ?> class="list-group-item list-group-item-action <?php if (!$days[2]) { ?>disabled<?php } ?>">
            Tuesday
          </a>
          <a href="#day-3" <?php if (!$days[3]) { ?>tabindex="-1" aria-disabled="true" <?php } ?> class="list-group-item list-group-item-action <?php if (!$days[3]) { ?>disabled<?php } ?>">
            Wednesday
          </a>
          <a href="#day-4" <?php if (!$days[4]) { ?>tabindex="-1" aria-disabled="true" <?php } ?> class="list-group-item list-group-item-action <?php if (!$days[4]) { ?>disabled<?php } ?>">
            Thursday
          </a>
          <a href="#day-5" <?php if (!$days[5]) { ?>tabindex="-1" aria-disabled="true" <?php } ?> class="list-group-item list-group-item-action <?php if (!$days[5]) { ?>disabled<?php } ?>">
            Friday
          </a>
          <a href="#day-6" <?php if (!$days[6]) { ?>tabindex="-1" aria-disabled="true" <?php } ?> class="list-group-item list-group-item-action <?php if (!$days[6]) { ?>disabled<?php } ?>">
            Saturday
          </a>
          <a href="#day-0" <?php if (!$days[0]) { ?>tabindex="-1" aria-disabled="true" <?php } ?> class="list-group-item list-group-item-action <?php if (!$days[0]) { ?>disabled<?php } ?>">
            Sunday
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();

?>