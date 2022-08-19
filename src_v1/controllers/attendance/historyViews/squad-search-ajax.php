<?php

$db = app()->db;
$tenant = app()->tenant;

$json = [
  'status' => 200,
  'html' => null,
  'alerts' => null,
];

// Verify squad
$getSquad = $db->prepare("SELECT SquadName FROM `squads` WHERE `SquadID` = ? AND Tenant = ?");
$getSquad->execute([
  $_POST['squad'],
  $tenant->getId()
]);
$squad = $getSquad->fetch(PDO::FETCH_ASSOC);

if ($squad == null) {
  reportError('NO SQUAD');
  halt(404);
}

header('content-type: application/json');

try {

  $getMembers = $db->prepare("SELECT MForename, MSurname, MemberID FROM squadMembers INNER JOIN members ON squadMembers.Member = members.MemberID WHERE squadMembers.Squad = ? ORDER BY MForename ASC, MSurname ASC");
  $getMembers->execute([
    $_POST['squad']
  ]);
  $member = $getMembers->fetch(PDO::FETCH_ASSOC);

  $startDate = new DateTime($_POST['from-date'], new DateTimeZone('Europe/London'));
  $endDate = new DateTime($_POST['to-date'], new DateTimeZone('Europe/London'));

  ob_start();
?>

  <div class="card mb-3">
    <div class="card-header">
      Statistics
    </div>
    <?php if ($member) { ?>
      <div class="card-body">
        <p class="lead text-muted">
          Stats for the period <?= htmlspecialchars($startDate->format('j F Y')) ?> to <?= htmlspecialchars($endDate->format('j F Y')) ?>
        </p>

        <p class="mb-0">
          Showing current squad members only.
        </p>

      </div>

      <ul class="list-group list-group-flush">

        <?php do {
          $history = AttendanceHistory::getHistory($member['MemberID'], $_POST['from-date'], $_POST['to-date']);
        ?>

          <li class="list-group-item">

            <h3><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?></h3>

            <div class="row">
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

          </li>

        <?php } while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)); ?>

      </ul>

      <div class="card-body">
        <p class="mb-0">
          Excused sessions have no impact on percentages as these sessions are not counted towards the total number of sessions available.
        </p>
      </div>
    <?php } else { ?>
      <div class="card-body">
        <div class="alert alert-warning mb-0">
          <p class="mb-0">
            <strong>No data to display</strong>
          </p>
          <p class="mb-0">
            There are currently no members in this squad.
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
