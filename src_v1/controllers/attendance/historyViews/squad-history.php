<?php

$db = app()->db;
$tenant = app()->tenant;

$now = new DateTime('now', new DateTimeZone('Europe/London'));
$startWeek = new DateTime('monday -1 week', new DateTimeZone('UTC'));
$now->setTimezone(new DateTimeZone('UTC'));

if ($now->format('l') == 'Monday') {
  $startWeek = $now;
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

$sessions = $pageSquad = null;

// Validate squad
$getSquad = $db->prepare("SELECT SquadName, SquadID FROM squads WHERE squads.Tenant = ? AND squads.SquadID = ?");
$getSquad->execute([
  $tenant->getId(),
  $id,
]);

$pageSquad = $getSquad->fetch(PDO::FETCH_ASSOC);

if (!$pageSquad) {
  halt(404);
}

$sessions = $db->prepare("SELECT * FROM ((`sessions` INNER JOIN sessionsSquads ON `sessions`.`SessionID` = sessionsSquads.Session) INNER JOIN sessionsVenues ON sessionsVenues.VenueID = sessions.VenueID) WHERE sessionsSquads.Squad = :squad AND sessions.Tenant = :tenant AND ((DisplayFrom <= :startTime AND DisplayUntil >= :endTime) OR ((:startTime <= DisplayFrom AND DisplayFrom <= :endTime) AND (:startTime <= DisplayUntil AND DisplayUntil <= :endTime))) ORDER BY SessionDay ASC, StartTime ASC, EndTime ASC;");
$sessions->execute([
  'squad' => (int) $id,
  'tenant' => $tenant->getId(),
  'startTime' => $startWeek->format('Y-m-d'),
  'endTime' => $endWeek->format('Y-m-d'),
]);

$getSessionSquads = $db->prepare("SELECT SquadName, ForAllMembers, SquadID FROM `sessionsSquads` INNER JOIN `squads` ON sessionsSquads.Squad = squads.SquadID WHERE sessionsSquads.Session = ? ORDER BY SquadFee DESC, SquadName ASC;");

$allSessions = $sessions->fetchAll(PDO::FETCH_ASSOC);

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

$getCoaches = $db->prepare("SELECT Forename fn, Surname sn, coaches.Type code FROM coaches INNER JOIN users ON coaches.User = users.UserID WHERE coaches.Squad = ? ORDER BY coaches.Type ASC, Forename ASC, Surname ASC");

// Get attendance
$getAttendees = $db->prepare("SELECT AttendanceBoolean, MForename fn, MSurname sn, members.MemberID mid FROM sessionsAttendance INNER JOIN members ON sessionsAttendance.MemberID = members.MemberID WHERE sessionsAttendance.SessionID = ? AND sessionsAttendance.WeekID = ? ORDER BY MForename ASC, MSurname ASC");

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
$pagetitle = 'Week ' . htmlspecialchars($startWeek->format('W')) . ', ' . htmlspecialchars($startWeek->format('o')) . ' - ' . htmlspecialchars($pageSquad['SquadName']) . " Session Attendance";

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance')) ?>">Attendance</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance/history')) ?>">History</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance/history/squads')) ?>">Squads</a></li>
        <li class="breadcrumb-item active" aria-current="page">Week</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col">
        <h1>
          Attendance at <?= htmlspecialchars($pageSquad['SquadName']) ?> sessions
        </h1>
        <p class="lead">
          Week <?= htmlspecialchars($startWeek->format('W')) ?> / <?= htmlspecialchars($startWeek->format('o')) ?>
        </p>

        <p class="mb-0">Week beginning <strong><?= htmlspecialchars($startWeek->format('l j F Y')) ?></strong>.</p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <!-- <h1>Attendance at <?= htmlspecialchars($pageSquad['SquadName']) ?> sessions</h1>
  <p class="lead">Week <?= htmlspecialchars($startWeek->format('W')) ?> / <?= htmlspecialchars($startWeek->format('o')) ?></p> -->

  <?php if ($sessionToday) { ?>
    <p><a href="#day-<?= $dayNum ?>">Jump to today</a></p>
  <?php } ?>

  <?php $weeks = clone $startWeek; ?>
  <?php $weeks->sub(new DateInterval('P7D')); ?>
  <div class="row mb-3">
    <div class="col-auto">
      <nav aria-label="Page navigation example">
        <ul class="pagination my-0">
          <li class="page-item">
            <a class="page-link" href="<?= htmlspecialchars(autoUrl("attendance/history/squads/$id/week?year=" . $weeks->format('o') . "&week=" . $weeks->format('W'))) ?>">
              Week <?= (int) $weeks->format('W') ?> / <?= $weeks->format('o') ?>
            </a>
          </li>
          <?php $weeks->add(new DateInterval('P7D')); ?>
          <li class="page-item">
            <a class="page-link" href="<?= htmlspecialchars(autoUrl("attendance/history/squads/$id/week?year=" . $weeks->format('o') . "&week=" . $weeks->format('W'))) ?>">
              Week <?= (int) $weeks->format('W') ?> / <?= $weeks->format('o') ?>
            </a>
          </li>
          <?php $weeks->add(new DateInterval('P7D')); ?>
          <li class="page-item">
            <a class="page-link" href="<?= htmlspecialchars(autoUrl("attendance/history/squads/$id/week?year=" . $weeks->format('o') . "&week=" . $weeks->format('W'))) ?>">
              Week <?= (int) $weeks->format('W') ?> / <?= $weeks->format('o') ?>
            </a>
          </li>
        </ul>
      </nav>
      <div class="mb-3 d-md-none"></div>
    </div>
    <div class="col-md">
      <form action="<?= htmlspecialchars(autoUrl('attendance/history/squads/' . $id . '/week/jump-to-week')) ?>" method="post" class="needs-validation" novalidate>
        <div class="input-group">
          <input type="date" class="form-control" value="<?= htmlspecialchars($startWeek->format('Y-m-d')) ?>" aria-label="Find a week" aria-describedby="go-to-week" name="go-to-week-date" id="go-to-week-date">
          <button class="btn btn-secondary" type="submit" id="go-to-week">Go to week</button>
        </div>
      </form>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8 order-2 order-lg-1">
      <?php $currentDay = $weekId = null; ?>

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
              $days[(int) $day->format('N') % 7] = true;

              try {
                $weekId = TrainingSession::weekId($day->format('Y-m-d'));
              } catch (Exception $e) {
                // Ignore
                $weekId = null;
              }

            ?>
              <div class="list-group-item bg-primary text-white" id="day-<?= htmlspecialchars($day->format('N') % 7) ?>">
                <h2 class="mb-0"><?= htmlspecialchars($day->format('l')) ?></h2>
                <p class="lead mb-0"><?= htmlspecialchars($day->format('j F Y')) ?></p>
              </div>
            <?php } ?>

            <div class="list-group-item">
              <h3 class="mb-0"><?php for ($i = 0; $i < sizeof($squadNames); $i++) { ?><?php if ($i > 0) { ?>, <?php } ?><?= htmlspecialchars($squadNames[$i]['SquadName']) ?><?php } ?></h3>
              <p class="h3"><small><?= htmlspecialchars($session['SessionName']) ?>, <?= htmlspecialchars($session['VenueName']) ?></small></p>

              <?php
              $startTime = new DateTime($session['StartTime'], new DateTimeZone('UTC'));
              $endTime = new DateTime($session['EndTime'], new DateTimeZone('UTC'));

              $getAttendees->execute([
                $session['SessionID'],
                $weekId,
              ]);

              $attendees = $getAttendees->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
              ?>

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
                      <?php for ($i = 0; $i < sizeof($coaches); $i++) { ?>
                        <li><strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($coaches[$i]['fn'], $coaches[$i]['sn'])) ?></strong>, <?= htmlspecialchars(coachTypeDescription($coaches[$i]['code'])) ?></li>
                      <?php } ?>
                      <?php if (sizeof($coaches) == 0) { ?>
                        <li>None assigned</li>
                      <?php } ?>
                    </ul>
                  </dd>

                <?php } ?>

                <?php

                // This is sensitive so hide if logged out

                ?>

                <dt class="col-sm-3">Location</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($session['Location']) ?></dd>

                <?php if ((isset($attendees['0']) && sizeof($attendees['0']) > 0) || (isset($attendees['1']) && sizeof($attendees['1']) > 0) || (isset($attendees['2']) && sizeof($attendees['2']) > 0)) { ?>
                  <dt class="col-sm-3">Links</dt>
                  <dd class="col-sm-9">
                    <ul class="list-unstyled mb-0">
                      <li><a href="<?= htmlspecialchars(autoUrl('attendance/register?date=' . urlencode($day->format('Y-m-d')) . '&session=' . $session['SessionID'])) ?>">Go to register</a></li>
                      <li><a href="<?= htmlspecialchars(autoUrl('covid/contact-tracing/reports/from-register?date=' . urlencode($day->format('Y-m-d')) . '&session=' . $session['SessionID'])) ?>">View COVID-19 contact details list</a></li>
                    </ul>
                  </dd>
                <?php } ?>

                <?php if (isset($attendees['1']) && sizeof($attendees['1']) > 0) { ?>
                  <dt class="col-sm-3">Present</dt>
                  <dd class="col-sm-9">
                    <ul class="list-unstyled mb-0">
                      <?php foreach ($attendees['1'] as $member) { ?>
                        <li><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['fn'], $member['sn'])) ?></li>
                      <?php } ?>
                    </ul>
                  </dd>
                <?php } ?>

                <?php if (isset($attendees['0']) && sizeof($attendees['0']) > 0) { ?>
                  <dt class="col-sm-3">Not present</dt>
                  <dd class="col-sm-9">
                    <ul class="list-unstyled mb-0">
                      <?php foreach ($attendees['0'] as $member) { ?>
                        <li><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['fn'], $member['sn'])) ?></li>
                      <?php } ?>
                    </ul>
                  </dd>
                <?php } ?>

                <?php if (isset($attendees['2']) && sizeof($attendees['2']) > 0) { ?>
                  <dt class="col-sm-3">Excused</dt>
                  <dd class="col-sm-9">
                    <ul class="list-unstyled mb-0">
                      <?php foreach ($attendees['2'] as $member) { ?>
                        <li><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['fn'], $member['sn'])) ?></li>
                      <?php } ?>
                    </ul>
                  </dd>
                <?php } ?>
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
      <nav aria-label="Page navigation example">
        <ul class="pagination">
          <li class="page-item">
            <a class="page-link" href="<?= htmlspecialchars(autoUrl("attendance/history/squads/$id/week?year=" . $weeks->format('o') . "&week=" . $weeks->format('W'))) ?>">
              Week <?= (int) $weeks->format('W') ?> / <?= $weeks->format('o') ?>
            </a>
          </li>
          <?php $weeks->add(new DateInterval('P7D')); ?>
          <li class="page-item">
            <a class="page-link" href="<?= htmlspecialchars(autoUrl("attendance/history/squads/$id/week?year=" . $weeks->format('o') . "&week=" . $weeks->format('W'))) ?>">
              Week <?= (int) $weeks->format('W') ?> / <?= $weeks->format('o') ?>
            </a>
          </li>
          <?php $weeks->add(new DateInterval('P7D')); ?>
          <li class="page-item">
            <a class="page-link" href="<?= htmlspecialchars(autoUrl("attendance/history/squads/$id/week?year=" . $weeks->format('o') . "&week=" . $weeks->format('W'))) ?>">
              Week <?= (int) $weeks->format('W') ?> / <?= $weeks->format('o') ?>
            </a>
          </li>
        </ul>
      </nav>
    </div>
    <div class="col order-1 order-lg-2">
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
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();

?>