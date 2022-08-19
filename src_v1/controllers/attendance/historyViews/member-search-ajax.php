<?php

$db = app()->db;
$tenant = app()->tenant;

$json = [
  'status' => 200,
  'html' => null,
  'alerts' => null,
];

// Verify member
$getMember = $db->prepare("SELECT MForename, MSurname FROM members WHERE MemberID = ? AND Tenant = ?");
$getMember->execute([
  $_POST['member'],
  $tenant->getId(),
]);
$member = $getMember->fetch(PDO::FETCH_ASSOC);

if (!$member) {
  halt(404);
}

header('content-type: application/json');

try {

  $startDate = new DateTime($_POST['from-date'], new DateTimeZone('Europe/London'));
  $endDate = new DateTime($_POST['to-date'], new DateTimeZone('Europe/London'));

  $history = AttendanceHistory::getHistory($_POST['member'], $_POST['from-date'], $_POST['to-date']);
  $data = $history->getData();

  ob_start();
?>

  <div class="card mb-3">
    <div class="card-header">
      Statistics
    </div>
    <div class="card-body">
      <p class="lead mb-3 text-muted">
        Stats for the period <?= htmlspecialchars($startDate->format('j F Y')) ?> to <?= htmlspecialchars($endDate->format('j F Y')) ?>
      </p>

      <div class="row mb-3">
        <div class="col-md">
          <h4>Mandatory sessions</h4>
          <dl class="row mb-0">
            <dt class="col-sm-7">Mandatory sessions attended</dt>
            <dd class="col-sm-5"><?= htmlspecialchars($history->getTotalMandatoryAttended()) ?></dd>

            <dt class="col-sm-7">Mandatory sessions excused</dt>
            <dd class="col-sm-5"><?= htmlspecialchars($history->getTotalMandatoryExcused()) ?></dd>

            <dt class="col-sm-7">Total mandatory sessions</dt>
            <dd class="col-sm-5"><?= htmlspecialchars($history->getTotalMandatorySessions()) ?></dd>

            <dt class="col-sm-7">Attendance percentage (mandatory sessions)</dt>
            <dd class="col-sm-5 mb-0"><?= htmlspecialchars(number_format($history->getPercentageTotal(), 1)) ?>%</dd>
          </dl>
          <div class="mb-3 d-md-none"></div>
        </div>

        <div class="col-md">
          <h4>All sessions</h4>
          <dl class="row mb-0">
            <dt class="col-sm-7">Sessions attended</dt>
            <dd class="col-sm-5"><?= htmlspecialchars($history->getTotalAttended()) ?></dd>

            <dt class="col-sm-7">Sessions excused</dt>
            <dd class="col-sm-5"><?= htmlspecialchars($history->getTotalExcused()) ?></dd>

            <dt class="col-sm-7">Total sessions</dt>
            <dd class="col-sm-5"><?= htmlspecialchars($history->getTotalSessions()) ?></dd>

            <dt class="col-sm-7">Attendance percentage (total sessions)</dt>
            <dd class="col-sm-5 mb-0"><?= htmlspecialchars(number_format($history->getPercentageMandatory(), 1)) ?>%</dd>
          </dl>
        </div>
      </div>

      <p class="mb-0">
        Excused sessions have no impact on percentages as these sessions are not counted towards the total number of sessions available.
      </p>
    </div>
  </div>

  <div class="card">

    <div class="card-header">
      Full Details
    </div>

    <?php if (sizeof($data) > 0) { ?>

      <div class="card-body">
        <p class="lead mb-0 text-muted">
          Sessions where registers were taken in the period <?= htmlspecialchars($startDate->format('j F Y')) ?> to <?= htmlspecialchars($endDate->format('j F Y')) ?>
        </p>
      </div>

      <ul class="list-group list-group-flush">
        <?php foreach ($data as $row) { ?>
          <li class="list-group-item">
            <pre class="mb-0"><?= json_encode($row, JSON_PRETTY_PRINT) ?></pre>
          </li>
        <?php } ?>
      </ul>
    <?php } else { ?>

      <div class="card-body">
        <div class="alert alert-warning mb-0">
          <p class="mb-0">
            <strong>No data to display</strong>
          </p>
          <p class="mb-0">
            No attendance records could be returned for the period <?= htmlspecialchars($startDate->format('j F Y')) ?> to <?= htmlspecialchars($endDate->format('j F Y')) ?>. Please try a new search.
          </p>
        </div>
      </div>

    <?php } ?>

  </div>

<?php
  $html = ob_get_clean();

  $json = [
    'status' => 200,
    'html' => $html,
    'alerts' => null,
    'dates' => [
      'start_date' => $startDate,
      'end_date' => $endDate,
    ],
    'data' => $data,
  ];
} catch (Exception $e) {

  reportError($e);

  $json = [
    'status' => 200,
    'html' => null,
    'alerts' => null,
  ];
}

echo json_encode($json);
