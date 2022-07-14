<?php

$db = app()->db;
$tenant = app()->tenant;

$day = (new DateTime('now', new DateTimeZone('Europe/London')))->format("w");
$time = (new DateTime('-15 minutes', new DateTimeZone('Europe/London')))->format("H:i:s");
$time30 = (new DateTime('-30 minutes', new DateTimeZone('Europe/London')))->format("H:i:s");

$sql = "SELECT SessionID, SessionName, VenueName, StartTime, EndTime FROM (`sessions` INNER JOIN sessionsVenues ON sessions.VenueID = sessionsVenues.VenueID) WHERE SessionDay = :day AND StartTime <= :timenow AND (EndTime > :timenow OR EndTime > :time30) AND DisplayFrom <= CURDATE() AND DisplayUntil >= CURDATE() AND `sessions`.`Tenant` = :tenant ORDER BY StartTime ASC, EndTime ASC";
$getSessionSquads = $db->prepare("SELECT SquadName, ForAllMembers FROM `sessionsSquads` INNER JOIN `squads` ON sessionsSquads.Squad = squads.SquadID WHERE sessionsSquads.Session = ? ORDER BY SquadFee DESC, SquadName ASC;");

$query = $db->prepare($sql);
$query->execute([
  'tenant' => $tenant->getId(),
  'day' => $day,
  'timenow' => $time,
  'time30' => $time30
]);
$sessions = $query->fetchAll(PDO::FETCH_ASSOC);
// $sessions = [];

$pagetitle = "Attendance";
include BASE_PATH . "views/header.php";
include "attendanceMenu.php"; ?>
<div class="front-page" style="margin-bottom: -1rem;">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Attendance</li>
      </ol>
    </nav>

    <h1>Attendance</h1>
    <p class="lead mb-4">View attendance records and fill out registers for squads</p>

    <?php if (sizeof($sessions) > 0) { ?>
      <?php $date = (new DateTime('now', new DateTimeZone('Europe/London')))->format("Y-m-d"); ?>
      <div class="mb-4">
        <h2 class="mb-4">Take Register for Current Sessions</h2>
        <div class="mb-4">
          <div class="news-grid">
            <?php for ($i = 0; $i < sizeof($sessions); $i++) {
              $getSessionSquads->execute([
                $sessions[$i]['SessionID'],
              ]);
              $squadNames = $getSessionSquads->fetchAll(PDO::FETCH_ASSOC);
            ?>
              <a href="<?= htmlspecialchars(autoUrl("attendance/register?date=" . urlencode($date) . "&session=" . urlencode($sessions[$i]['SessionID']))) ?>" title="<?= htmlspecialchars($sessions[$i]['SquadName']) ?> Register, <?= htmlspecialchars($sessions[$i]['SessionName']) ?>">
                <div>
                  <span class="title mb-0">
                    Take <?php for ($y = 0; $y < sizeof($squadNames); $y++) { ?><?php if ($y > 0) { ?>, <?php } ?><?= htmlspecialchars($squadNames[$y]['SquadName']) ?><?php } ?> Register
                  </span>
                  <span class="d-flex mb-3">
                    <?= date("H:i", strtotime($sessions[$i]['StartTime'])) ?> - <?= date("H:i", strtotime($sessions[$i]['EndTime'])) ?>
                  </span>
                </div>
                <span class="category">
                  <?= htmlspecialchars($sessions[$i]['SessionName']) ?>, <?= htmlspecialchars($sessions[$i]['VenueName']) ?>
                </span>
              </a>
            <?php } ?>
          </div>
        </div>
      </div>
    <?php } ?>

    <div class="mb-4">
      <?php if (sizeof($sessions) > 0) { ?>
        <h2 class="mb-4">Further Attendance Options</h2>
      <?php } ?>
      <div class="news-grid">
        <a href="<?= htmlspecialchars(autoUrl("attendance/register")) ?>">
          <div>
            <span class="title mb-0">
              Take a Register
            </span>
            <span class="d-flex mb-3">
              Quickly take a register for any squad
            </span>
          </div>
          <span class="category">
            Attendance
          </span>
        </a>

        <a href="<?= htmlspecialchars(autoUrl("attendance/history/members")) ?>">
          <div>
            <span class="title mb-0">
              Member Attendance
            </span>
            <span class="d-flex mb-3">
              View member attendance records for up to the last twenty weeks
            </span>
          </div>
          <span class="category">
            Attendance
          </span>
        </a>

        <a href="<?= htmlspecialchars(autoUrl("attendance/history/squads")) ?>">
          <div>
            <span class="title mb-0">
              Squad Attendance
            </span>
            <span class="d-flex mb-3">
              View squad attendance for the current week
            </span>
          </div>
          <span class="category">
            Attendance
          </span>
        </a>

        <a href="<?= htmlspecialchars(autoUrl("timetable/booking")) ?>">
          <div>
            <span class="title mb-0">
              Session Booking
            </span>
            <span class="d-flex mb-3">
              Book a session
            </span>
          </div>
          <span class="category">
            Attendance
          </span>
        </a>
      </div>
    </div>

  </div>
</div>
<?php $footer = new \SCDS\Footer();
$footer->render();
