<?php

$db = app()->db;
$tenant = app()->tenant;

// Get the last four weeks to calculate attendance
$sql = $db->prepare("SELECT `WeekID` FROM `sessionsWeek` WHERE Tenant = ? ORDER BY `WeekDateBeginning` DESC LIMIT 1 OFFSET 20");
$sql->execute([
	$tenant->getId()
]);
$earliestWeek = $sql->fetchColumn();

if ($earliestWeek == null) {
	$sql = $db->prepare("SELECT `WeekID` FROM `sessionsWeek` WHERE Tenant = ? ORDER BY `WeekDateBeginning` ASC LIMIT 1");
	$sql->execute([
		$tenant->getId()
	]);
	$earliestWeek = $sql->fetchColumn();
}

if ($earliestWeek == null) {
	// No weeks
}

$getMember = $db->prepare("SELECT MForename first, MSurname last FROM `members` WHERE `MemberID` = ? AND Tenant = ?");
$getMember->execute([
	$id,
	$tenant->getId()
]);
$member = $getMember->fetch(PDO::FETCH_ASSOC);

if ($member == null) {
	halt(404);
}

$pagetitle = htmlspecialchars($member['first'] . " " . $member['last']) . " Attendance History";

$getPresent = $db->prepare("SELECT * FROM (`sessionsAttendance` INNER JOIN `sessions` ON sessionsAttendance.SessionID=sessions.SessionID) WHERE WeekID >= ? AND `MemberID` = ? ORDER BY WeekID DESC, SessionDay DESC, StartTime DESC");
if ($earliestWeek != null) {
	$getPresent->execute([
		$earliestWeek,
		$id
	]);
}

$present = $getPresent->fetch(PDO::FETCH_ASSOC);

include BASE_PATH . "views/header.php";
include BASE_PATH . "controllers/attendance/attendanceMenu.php"; ?>

<div class="bg-light mt-n3 py-3 mb-3">
	<div class="container-xl">

		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance')) ?>">Attendance</a></li>
				<li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance/history')) ?>">History</a></li>
				<li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance/history/swimmers')) ?>">Members</a></li>
				<li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars(mb_substr($member['first'], 0, 1) . mb_substr($member['last'], 0, 1)) ?></li>
			</ol>
		</nav>

		<div class="row align-items-center">
			<div class="col">
				<h1>
					<?= htmlspecialchars($member['first'] . " " . $member['last']) ?>'s Attendance History
				</h1>
				<p class="lead mb-0">
					Up to the last 20 weeks
				</p>
			</div>
		</div>
	</div>
</div>

<div class="container-xl">

	<?php if ($present == null) { ?>
		<div class="alert alert-warning">
			<p class="mb-0"><strong>No information available</strong></p>
			<p class="mb-0">This is likely because no registers have been taken at sessions this swimmer could attend.</p>
		</div>
	<?php } else { ?>

		<div class="table-responsive-md">
			<table class="table">
				<thead>
					<tr>
						<th>Session</th>
						<th>Attendance</th>
					</tr>
				</thead>
				<tbody>

					<?php do {
						$sessionID = $present['SessionID'];
						$weekID = $present['WeekID'];
						$details = $db->prepare("SELECT * FROM ((`sessionsAttendance` INNER JOIN sessions ON sessions.SessionID=sessionsAttendance.sessionID) INNER JOIN sessionsWeek ON sessionsWeek.WeekID=sessionsAttendance.WeekID) WHERE sessionsAttendance.SessionID = ? AND MemberID = ? AND sessionsAttendance.WeekID = ?");
						$details->execute([
							$sessionID,
							$id,
							$weekID
						]);
						$sessionInfo = $details->fetch(PDO::FETCH_ASSOC);

						$weekBeginning = $sessionInfo['WeekDateBeginning'];
						$dayAdd = $sessionInfo['SessionDay'];
						$date = date('j F Y', strtotime($weekBeginning . ' + ' . $dayAdd . ' days'));

						$dayText = "";
						switch ($sessionInfo['SessionDay']) {
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

					?>

						<tr class="<?php if ($present['AttendanceBoolean'] == 1) { ?> table-success <?php } else if ($present['AttendanceBoolean'] == 2) { ?> table-active <?php } else { ?> table-danger <?php } ?>">
							<td>
								<?= htmlspecialchars($sessionInfo['SessionName']) ?>, <?= $dayText ?> <?= $date ?> at <?= $sessionInfo['StartTime'] ?>
								<?php if ($present['AttendanceRequired'] != 1) { ?>
									(Not Mandatory)
								<?php } ?>
							</td>
							<td>

								<?php if ($present['AttendanceBoolean'] == 1) { ?>
									<div>
										&#10003;
									</div>
								<?php } else if ($present['AttendanceBoolean'] == 2) { ?>
									Excused
								<?php } else { ?>
									<div class="d-print-none">

									</div>
								<?php } ?>
							</td>
						</tr>
					<?php } while ($present = $getPresent->fetch(PDO::FETCH_ASSOC)); ?>
				</tbody>
			</table>
		</div>

	<?php } ?>

</div>
<?php $footer = new \SCDS\Footer();
$footer->render();
