<?php

$db = app()->db;
$tenant = app()->tenant;

$latestWeek = $db->prepare("SELECT WeekID, WeekDateBeginning FROM `sessionsWeek` WHERE Tenant = ? ORDER BY `WeekDateBeginning` DESC LIMIT 1;");
$latestWeek->execute([
	$tenant->getId()
]);
$latestWeek = $latestWeek->fetch(PDO::FETCH_ASSOC);
$week = (int) $latestWeek['WeekID'];
$weekDateBeginning = $latestWeek['WeekDateBeginning'];

$getSquadName = $db->prepare("SELECT SquadName FROM `squads` WHERE `SquadID` = ? AND Tenant = ?");
$getSquadName->execute([
	$id,
	$tenant->getId()
]);
$squadName = $getSquadName->fetchColumn();

if (!$squadName) {
	halt(404);
}

$get = $db->prepare("SELECT * FROM ((`members` LEFT JOIN `sessionsAttendance` ON
`sessionsAttendance`.`MemberID` = `members`.`MemberID`) INNER JOIN `sessions` ON
`sessionsAttendance`.`SessionID` = `sessions`.`SessionID` INNER JOIN sessionsSquads ON sessions.SessionID = sessionsSquads.Squad) WHERE
`sessionsSquads`.`Squad` = ? AND `WeekID` = ? AND members.Tenant = ? ORDER BY `MForename` ASC,
`MSurname` ASC, `SessionDay` ASC, `StartTime` ASC");
$get->execute([$id, $week, $tenant->getId()]);

$row = $get->fetch(PDO::FETCH_ASSOC);

$swimmerOld = null;

$pagetitle = "Attendance history for " . htmlspecialchars($squadName) . ' Squad';
include BASE_PATH . "views/header.php";
include BASE_PATH . "controllers/attendance/attendanceMenu.php"; ?>

<div class="bg-light mt-n3 py-3 mb-3">
	<div class="container-xl">

		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance')) ?>">Attendance</a></li>
				<li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance/history')) ?>">History</a></li>
				<li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance/history/squads')) ?>">Squads</a></li>
				<li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($squadName) ?></li>
			</ol>
		</nav>

		<div class="row align-items-center">
			<div class="col">
				<h1>
					Attendance history for <?= htmlspecialchars($squadName) ?>
				</h1>
				<p class="lead mb-0">
					Squad history currently only shows the current week
				</p>
			</div>
		</div>
	</div>
</div>

<div class="container-xl">
	<div class="">

		<?php if ($row == null) { ?>
			<div class="alert alert-warning">
				<strong>No swimmers or recorded sessions were found for <?= htmlspecialchars($squadName) ?></strong><br>
				You need to take a register before swimmers will appear here
			</div>
		<?php } else { ?>

			<div class="table-responsive-md">
				<table class="table table-sm table-light">
					<thead class="">
						<tr>
							<th>
								Swimmer
							</th>
							<th>
								Sessions
							</th>
						</tr>
					</thead>
					<tbody>
						<?php do {
							$outputname = false;
							$swimmer = $row['MemberID'];
							if ($swimmer != $swimmerOld) {
								$outputname = true;
							}
							if ($outputname) {
						?>
								<tr>
									<td>
										<?= htmlspecialchars(\SCDS\Formatting\Names::format($row['MForename'], $row['MSurname'])) ?>
									</td>
									<td>
										<ul class="list-unstyled mb-0">
										<?php
									}

									$dayAdd = $row['SessionDay'];
									$date = new DateTime($weekDateBeginning, new DateTimeZone('Europe/London'));
									$date->add(new DateInterval('P' . $dayAdd . 'D'));
									$date = $date->format('j F Y');

									$dayText = "";
									switch ($row['SessionDay']) {
										case 0:
											$dayText = "Sunday";
											break;
										case 1:
											$dayText = "Monday";
											break;
										case 2:
											$dayText = "Tuesday";
											break;
										case 3:
											$dayText = "Wednesday";
											break;
										case 4:
											$dayText = "Thursday";
											break;
										case 5:
											$dayText = "Friday";
											break;
										case 6:
											$dayText = "Saturday";
											break;
									}

									$datetime1 = new DateTime($row['StartTime']);
									$title = $row['SessionName'] . " on " . $dayText . " " . $date . " at " . $datetime1->format("H:i");

										?>
										<li>
											<?php if ($row['AttendanceBoolean']) { ?>
												<?= htmlspecialchars($title) ?>
											<?php } ?>
										</li>
										<?php

										$swimmerOld = $row['MemberID'];
										if ($swimmer != $swimmerOld) {
										?>
										</ul>
									</td>
								</tr><?php
										}
									} while ($row = $get->fetch(PDO::FETCH_ASSOC)); ?>
					</tbody>
				</table>
			</div>

		<?php } ?>

	</div>

</div>
<?php $footer = new \SCDS\Footer();
$footer->render();
?>