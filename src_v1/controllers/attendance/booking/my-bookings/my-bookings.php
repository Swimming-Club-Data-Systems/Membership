<?php

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

$start = 0;
$page = 0;

if (isset($_GET['page']) && ((int) $_GET['page']) != 0) {
  $page = (int) $_GET['page'];
  $start = ($page - 1) * 10;
} else {
  $page = 1;
}

$date = new DateTime('now', new DateTimeZone('Europe/London'));
$closedBookingsTime = new DateTime('+15 minutes', new DateTimeZone('Europe/London'));

$getBookingsCount = $db->prepare("SELECT COUNT(DISTINCT(CONCAT(sessionsBookings.Date,'-S',CAST(sessionsBookings.Session AS CHAR)))) AS SessionID, sessionsBookings.Date, sessionsBookings.Session FROM sessionsBookings INNER JOIN sessions ON sessions.SessionID = sessionsBookings.Session INNER JOIN members ON members.MemberID = sessionsBookings.Member WHERE members.UserID = ? AND sessionsBookings.Date >= ? ORDER BY sessionsBookings.Date ASC, StartTime ASC, EndTime ASC, MForename ASC, MSurname ASC;");
$getBookingsCount->execute([
  $user->getId(),
  $date->format('Y-m-d'),
]);

$numBookings  = $getBookingsCount->fetchColumn();
$numPages = ((int)($numBookings / 10)) + 1;

$getBookings = $db->prepare("SELECT DISTINCT(CONCAT(sessionsBookings.Date,'-S',CAST(sessionsBookings.Session AS CHAR))) AS SessionID, sessionsBookings.Date, sessionsBookings.Session, sessions.SessionName, sessionsVenues.VenueName, sessionsVenues.Location, sessions.StartTime, sessions.EndTime FROM sessionsBookings INNER JOIN sessions ON sessions.SessionID = sessionsBookings.Session INNER JOIN members ON members.MemberID = sessionsBookings.Member INNER JOIN sessionsVenues ON sessions.VenueID = sessionsVenues.VenueID WHERE members.UserID = :user AND sessionsBookings.Date >= :dateString ORDER BY sessionsBookings.Date ASC, StartTime ASC, EndTime ASC, MForename ASC, MSurname ASC LIMIT :offset, :num");
$getBookings->bindValue(':user', $user->getId(), PDO::PARAM_INT);
$getBookings->bindValue(':dateString', $date->format('Y-m-d'), PDO::PARAM_STR);
$getBookings->bindValue(':offset', $start, PDO::PARAM_INT);
$getBookings->bindValue(':num', 10, PDO::PARAM_INT);
$getBookings->execute();
$booking = $getBookings->fetch(PDO::FETCH_ASSOC);

$getBookingRequired = $db->prepare("SELECT COUNT(*) FROM `sessionsBookable` INNER JOIN `sessions` ON `sessions`.`SessionID` = `sessionsBookable`.`Session` WHERE `sessionsBookable`.`Session` = ? AND `sessionsBookable`.`Date` = ? AND `sessions`.`Tenant` = ?");

$getSessionSquads = $db->prepare("SELECT SquadName, ForAllMembers, SquadID FROM `sessionsSquads` INNER JOIN `squads` ON sessionsSquads.Squad = squads.SquadID WHERE sessionsSquads.Session = ? ORDER BY SquadFee DESC, SquadName ASC;");

$getCoaches = $db->prepare("SELECT Forename fn, Surname sn, coaches.Type code FROM coaches INNER JOIN users ON coaches.User = users.UserID WHERE coaches.Squad = ? ORDER BY coaches.Type ASC, Forename ASC, Surname ASC");

$getMyBookedMembers = $db->prepare("SELECT MForename fn, MSurname sn, BookedAt, members.MemberID id FROM sessionsBookings INNER JOIN members ON members.MemberID = sessionsBookings.Member WHERE members.UserID = ? AND sessionsBookings.Session = ? AND sessionsBookings.Date = ?");

$pagetitle = 'Session Booking';
include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('timetable')) ?>">Timetable</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('timetable/booking')) ?>">Booking</a></li>
        <li class="breadcrumb-item active" aria-current="page">My Bookings</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col">
        <h1>
          My Bookings
        </h1>
        <p class="lead mb-0">
          View your current and upcoming session bookings
        </p>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <div class="row mb-3">
        <div class="col">
          <p class="lead mb-0">
            Page <?= htmlspecialchars($page) ?> of <?= htmlspecialchars($numPages) ?>
          </p>
        </div>
        <div class="col text-end">
          <p class="lead text-muted mb-0">
            <?= htmlspecialchars($numBookings) ?> booking<?php if ($numBookings != 1) { ?>s<?php } ?> in total
          </p>
        </div>
      </div>

      <!-- Results -->
      <?php if ($booking) { ?>
        <ul class="list-group mb-3">
          <?php do {

            $sessionDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $booking['Date'] .  '-' . $booking['StartTime'], new DateTimeZone('Europe/London'));
            $startTime = new DateTime($booking['StartTime'], new DateTimeZone('Europe/London'));
            $endTime = new DateTime($booking['EndTime'], new DateTimeZone('Europe/London'));

            $getSessionSquads->execute([
              $booking['Session'],
            ]);
            $squadNames = $getSessionSquads->fetchAll(PDO::FETCH_ASSOC);

            $getMyBookedMembers->execute([
              $user->getId(),
              $booking['Session'],
              $booking['Date'],
            ]);
            $bookedMember = $getMyBookedMembers->fetch(PDO::FETCH_ASSOC);

          ?>
            <li class="list-group-item" id="<?= htmlspecialchars('session-unique-id-' . $booking['SessionID']) ?>">
              <h2 class="mb-0"><?php if (sizeof($squadNames) > 0) { ?><?php for ($i = 0; $i < sizeof($squadNames); $i++) { ?><?php if ($i > 0) { ?>, <?php } ?><?= htmlspecialchars($squadNames[$i]['SquadName']) ?><?php } ?><?php } else { ?>Any Member<?php } ?></h2>
              <p class="h3"><small><?= htmlspecialchars($booking['SessionName']) ?>, <?= htmlspecialchars($booking['VenueName']) ?></small></p>

              <dl class="row mb-0">
                <dt class="col-sm-3">Date</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($sessionDateTime->format('l j F Y')) ?></dd>

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
                  $booking['Session'],
                  $sessionDateTime->format('Y-m-d'),
                  $tenant->getId(),
                ]);
                $bookingRequired = $getBookingRequired->fetchColumn() > 0;
                ?>
                <dt class="col-sm-3">Booking</dt>
                <dd class="col-sm-9">
                  <?php if ($futureSession) { ?>
                    <span class="d-block mb-2">Booking is required for this session</span>
                    <a href="<?= htmlspecialchars(autoUrl('timetable/booking/book?session=' . urlencode($booking['Session']) . '&date=' . urlencode($sessionDateTime->format('Y-m-d')))) ?>" class="btn btn-success">Manage bookings for this session</a>
                  <?php } else { ?>
                    <span class="d-block">Booking has closed for this session. No changes can be made.</span>
                  <?php } ?>
                </dd>

                <?php if ($bookedMember) { ?>
                  <dt class="col-sm-3">Booked Members</dt>
                  <dd class="col-sm-9">
                    <div class="list-group">
                      <?php do {
                        $bookedAt = new DateTime($bookedMember['BookedAt'], new DateTimeZone('UTC'));
                        $bookedAt->setTimezone(new DateTimeZone('Europe/London'));
                      ?>
                        <a href="<?= htmlspecialchars(autoUrl('members/' . $bookedMember['id'])) ?>" class="list-group-item list-group-item-action">
                          <span class="mb-0 d-block">
                            <strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($bookedMember['fn'], $bookedMember['sn'])) ?></strong>
                          </span>
                          <span class="mb-0 d-block">
                            <em>Booked at <?= htmlspecialchars($bookedAt->format('H:i, j F Y')) ?></em>
                          </span>
                        </a>
                      <?php } while ($bookedMember = $getMyBookedMembers->fetch(PDO::FETCH_ASSOC)); ?>
                    </div>
                  </dd>
                <?php } ?>

                <?php
                // IN FUTURE: LINK TO A LOCATION PAGE
                // GEOCODE AND USE A MAP
                ?>
                <dt class="col-sm-3">Location</dt>
                <dd class="col-sm-9 mb-0"><?= htmlspecialchars($booking['VenueName']) ?>, <em><?= htmlspecialchars($booking['Location']) ?></em></dd>
              </dl>
            </li>
          <?php } while ($booking = $getBookings->fetch(PDO::FETCH_ASSOC)); ?>
        </ul>
      <?php } else { ?>
      <div class="alert alert-info">
        <p class="mb-0">
          <strong>No bookings to display</strong>
        </p>
        <p class="mb-0">
          Book a place on a future session and then check back here later.
        </p>
      </div>
      <?php } ?>

      <!-- Pagination -->
      <nav aria-label="Page navigation">
        <ul class="pagination mb-3">
          <?php if ($numBookings <= 10) { ?>
            <li class="page-item active"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page))) ?>"><?= htmlspecialchars($page) ?></a></li>
          <?php } else if ($numBookings <= 20) { ?>
            <?php if ($page == 1) { ?>
              <li class="page-item active"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page))) ?>"><?= htmlspecialchars($page) ?></a></li>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page + 1))) ?>"><?= htmlspecialchars($page + 1) ?></a></li>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page + 1))) ?>">Next</a></li>
            <?php } else { ?>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page - 1))) ?>">Previous</a></li>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page - 1))) ?>"><?= htmlspecialchars($page - 1) ?></a></li>
              <li class="page-item active"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page + 1))) ?><?= htmlspecialchars($page) ?>"><?= htmlspecialchars($page) ?></a></li>
            <?php } ?>
          <?php } else { ?>
            <?php if ($page == 1) { ?>
              <li class="page-item active"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page))) ?>"><?= htmlspecialchars($page) ?></a></li>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page + 1))) ?>"><?= htmlspecialchars($page + 1) ?></a></li>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page + 2))) ?>"><?= htmlspecialchars($page + 2) ?></a></li>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page + 1))) ?>">Next</a></li>
            <?php } else { ?>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page - 1))) ?>">Previous</a></li>
              <?php if ($page > 2) { ?>
                <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page - 2))) ?>"><?= htmlspecialchars($page - 2) ?></a></li>
              <?php } ?>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page - 1))) ?>"><?= htmlspecialchars($page - 1) ?></a></li>
              <li class="page-item active"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page))) ?>"><?= htmlspecialchars($page) ?></a></li>
              <?php if ($numBookings > $page * 10) { ?>
                <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page + 1))) ?>"><?= htmlspecialchars($page + 1) ?></a></li>
                <?php if ($numBookings > $page * 10 + 10) { ?>
                  <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page + 2))) ?>"><?= htmlspecialchars($page + 2) ?></a></li>
                <?php } ?>
                <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings?page=' . urlencode($page + 1))) ?>">Next</a></li>
              <?php } ?>
            <?php } ?>
          <?php } ?>
        </ul>
      </nav>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
