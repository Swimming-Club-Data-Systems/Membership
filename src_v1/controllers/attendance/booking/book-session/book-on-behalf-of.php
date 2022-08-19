<?php

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

if (!isset($_GET['session']) && !isset($_GET['date'])) halt(404);

$date = null;
try {
  $date = new DateTime($_GET['date'], new DateTimeZone('Europe/London'));
} catch (Exception $e) {
  halt(404);
}

// Get session
$getSession = $db->prepare("SELECT `SessionID`, `SessionName`, `DisplayFrom`, `DisplayUntil`, `StartTime`, `EndTime`, `VenueName`, `Location`, `SessionDay`, `MaxPlaces`, `AllSquads`, `RegisterGenerated`, `BookingOpens` FROM `sessionsBookable` INNER JOIN `sessions` ON sessionsBookable.Session = sessions.SessionID INNER JOIN `sessionsVenues` ON `sessions`.`VenueID` = `sessionsVenues`.`VenueID` WHERE `sessionsBookable`.`Session` = ? AND `sessionsBookable`.`Date` = ? AND `sessions`.`Tenant` = ? AND DisplayFrom <= ? AND DisplayUntil >= ?");
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
$theLink = autoUrl('sessions/booking/book?session=' . urlencode($session['SessionID']) . '&date=' . urlencode($date->format('Y-m-d')));

// Get session squads
$getSquads = $db->prepare("SELECT `Squad` FROM `sessionsSquads` WHERE `Session` = ?");
$getSquads->execute([
  $session['SessionID'],
]);

while ($squad = $getSquads->fetchColumn()) {
  // Get members for this user in this squad
  $getMembers = $db->prepare("SELECT MForename fn, MSurname sn, MemberID id FROM members INNER JOIN squadMembers ON members.MemberID = squadMembers.Member WHERE squadMembers.Squad = ? ORDER BY fn ASC, sn ASC");
  $getMembers->execute([
    $squad,
  ]);

  while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)) {
    $members[] = $member;
  }
}

// Remove duplicated
// $members = array_unique($members);
$members = array_map("unserialize", array_unique(array_map("serialize", $members)));

$getBooking = $db->prepare("SELECT `BookedAt` FROM `sessionsBookings` WHERE `Session` = ? AND `Date` = ? AND `Member` = ?");

$pagetitle = 'Book on behalf of - Session Booking';
include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('timetable')) ?>">Timetable</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('timetable/booking')) ?>">Booking</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('timetable/booking/book?session=' . urlencode($session['SessionID']) . '&date=' . urlencode($date->format('Y-m-d')))) ?>"><?= htmlspecialchars($date->format('Y-m-d')) ?>-S<?= htmlspecialchars($session['SessionID']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><abbr title="Book On Behalf Of">BOBO</abbr></li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          <?= htmlspecialchars($session['SessionName']) ?> on <?= htmlspecialchars($date->format('j F Y')) ?>
        </h1>
        <p class="lead mb-0">
          <?php if ($session['MaxPlaces']) { ?>There are <?= htmlspecialchars($numFormatter->format($session['MaxPlaces'])) ?> places at this session<?php } else { ?>There are unlimited places at this session<?php } ?>
        </p>
        <div class="mb-3 d-lg-none"></div>
      </div>
      <div class="col text-lg-end">
        <?php if ($user->hasPermission('Admin') || $user->hasPermission('Coach')) { ?>
          <a href="<?= htmlspecialchars(autoUrl('sessions/booking/book?session=' . urlencode($session['SessionID']) . '&date=' . urlencode($date->format('Y-m-d')))) ?>" class="btn btn-dark-l btn-outline-light-d" title="Changes won't be saved">
            Back
          </a>
        <?php } ?>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8 order-2 order-lg-1 mb-3">
      <p class="lead d-none d-lg-block">
        <span class="place-numbers-places-booked-string uc-first"><?= htmlspecialchars(mb_ucfirst($numFormatter->format($bookedCount))) ?></span> <span id="place-numbers-booked-places-member-string"><?php if ($bookedCount == 1) { ?>member has<?php } else { ?>members have<?php } ?></span> booked onto this session. <?php if ($session['MaxPlaces']) { ?><span class="place-numbers-places-remaining-string uc-first"><?= htmlspecialchars(mb_ucfirst($numFormatter->format($session['MaxPlaces'] - $bookedCount))) ?></span> <span id="place-numbers-places-remaining-member-string"><?php if (($session['MaxPlaces'] - $bookedCount) == 1) { ?>place remains<?php } else { ?>places remain<?php } ?></span> available.<?php } ?>
      </p>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['BookOnBehalfOfSuccess'])) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>Members booked successfully</strong>
          </p>
          <p class="mb-0">
            We'll send an email to members to let them know you have booked them a place on their behalf.
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['BookOnBehalfOfSuccess']); } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['BookOnBehalfOfError'])) { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>We could not book all members a place</strong>
          </p>
          <p class="mb-0">
            Check the list below.
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['BookOnBehalfOfError']); } ?>

      <?php if ($bookingClosed) { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>Booking has closed for this session</strong>
          </p>
          <p class="mb-0">
            Booking closed automatically at <?= htmlspecialchars($bookingCloses->format('H:i, j F Y (T)')) ?>, 15 minutes prior to the published session start time of <?= htmlspecialchars($sessionDateTime->format('H:i T')) ?>.
          </p>
        </div>
      <?php } else if (sizeof($members) > 0) { ?>

        <h2>Select members to add</h2>
        <p class="lead">
          Select members to book a place on their behalf.
        </p>

        <p>
          Please only do this for a limited number of members.
        </p>

        <form method="post">
          <ul class="list-group mb-3">
            <?php foreach ($members as $member) {
              $getBooking->execute([
                $session['SessionID'],
                $date->format('Y-m-d'),
                $member['id'],
              ]);
              $booking = $getBooking->fetchColumn();
            ?>
              <li class="list-group-item <?php if ($booking) { ?>bg-light text-muted user-select-none<?php } ?>" id="<?= htmlspecialchars('member-box-for-member-' . $member['id']) ?>">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="<?= htmlspecialchars('member-checkbox-' . $member['id']) ?>" id="<?= htmlspecialchars('member-checkbox-' . $member['id']) ?>" <?php if ($booking) { ?>checked disabled<?php } ?>>
                  <label class="form-check-label" for="<?= htmlspecialchars('member-checkbox-' . $member['id']) ?>"><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['fn'], $member['sn'])) ?></label>
                </div>
              </li>
            <?php } ?>
          </ul>

          <p>
            We will send an email to all selected members notifying them that you have pre-booked a slot on their behalf.
          </p>

          <p>
            <button type="submit" class="btn btn-primary">
              Add bookings
            </button>
          </p>

        </form>

      <?php } else { ?>
      <?php } ?>



    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
