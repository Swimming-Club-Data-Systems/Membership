<?php

$pagetitle = "Attendance History";

include BASE_PATH . "views/header.php";
include BASE_PATH . "controllers/attendance/attendanceMenu.php";

?>

<div class="front-page mb-n3">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance')) ?>">Attendance</a></li>
        <li class="breadcrumb-item active" aria-current="page">History</li>
      </ol>
    </nav>

    <h1>Attendance History</h1>
    <p class="lead">
      Member attendance records
    </p>

    <div class="news-grid">
      <a href="<?= htmlspecialchars(autoUrl("attendance/history/members")) ?>">
        <span class="title mb-0">Member Attendance</span>
        <span>View attendance records for up to the last 20 weeks</span>
      </a>
      <a href="<?= htmlspecialchars(autoUrl("attendance/history/squads")) ?>">
        <span class="title mb-0">Squad Attendance</span>
        <span>View attendance for previous sessions across full squads</span>
      </a>
    </div>

  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
