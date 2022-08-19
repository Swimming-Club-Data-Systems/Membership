<?php

$db = app()->db;
$tenant = app()->tenant;

$sql = $db->prepare("SELECT * FROM galas WHERE galas.GalaID = ? AND Tenant = ?");
$sql->execute([
	$id,
	$tenant->getId()
]);
$info = $sql->fetch(PDO::FETCH_ASSOC);

$noTimeSheet = false;

if ($info = null) {
	halt(404);
	$noTimeSheet = true;
}

$sql = null;

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
	$sql = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE galas.GalaID = ? AND members.UserID = ? ORDER BY members.MForename ASC, members.MSurname ASC;");
	$sql->execute([$id, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
} else {
	$sql = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE galas.GalaID = ? ORDER BY members.MForename ASC, members.MSurname ASC;");
	$sql->execute([$id]);
}

$row = $sql->fetch(PDO::FETCH_ASSOC);

if ($row == null) {
	$noTimeSheet = true;
}

if ($noTimeSheet) {
	$pagetitle = "Galas";
	include BASE_PATH . "views/header.php";
	include "galaMenu.php"; ?>
	<div class="container-xl">
		<h1>There is no Time Sheet available for the gala you requested</h1>
		<?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
		?><p class="lead">This may be because your swimmers have not entered this gala.</p>
		<?php } else {
		?><p class="lead">There are no entries yet for this gala.</p>
		<?php } ?>
	</div>
<?php
	$footer = new \SCDS\Footer();
	$footer->render();
} else {
	// output headers so that the file is downloaded rather than displayed
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=' . str_replace(' ', '', $info['GalaName']) . '-TimeSheet.csv');

	// create a file pointer connected to the output stream
	$output = fopen('php://output', 'w');

	fputcsv($output, array(app()->tenant->getKey('CLUB_NAME') . ' Gala Time Sheet'));
	fputcsv($output, array($info['GalaName'] . " - " . $info['GalaVenue'] . " - " . date("d/m/Y", strtotime($info['GalaDate']))));
	fputcsv($output, array('Time Sheet Report Generated on ' . date("d/m/Y, H:i")));
	fputcsv($output, array(''));

	$swimsArray = ['25Free', '50Free', '100Free', '200Free', '400Free', '800Free', '1500Free', '25Back', '50Back', '100Back', '200Back', '25Breast', '50Breast', '100Breast', '200Breast', '25Fly', '50Fly', '100Fly', '200Fly', '100IM', '150IM', '200IM', '400IM',];
	$swimsTextArray = ['25 Fr', '50 Fr', '100 Fr', '200 Fr', '400 Fr', '800 Fr', '1500 Fr', '25 Bk', '50 Bk', '100 Bk', '200 Bk', '25 Br', '50 Br', '100 Br', '200 Br', '25 Fly', '50 Fly', '100 Fly', '200 Fly', '100 IM', '200 IM', '400 IM'];

	fputcsv($output, array('Forename', 'Surname', 'Swims'));

	// loop over the rows, outputting them
	do {
		$row['MemberID'];
		$typeA = $typeB = null;
		if ($info['CourseLength'] == "SHORT") {
			$typeA = "SCPB";
			$typeB = "CY_SC";
		} else {
			$typeA = "LCPB";
			$typeB = "CY_LC";
		}

		//pre([$timesPB, $timesCY]);

		$swims = [
			$row['MForename'],
			$row['MSurname'],
			'Event'
		];

		$timesArrayA = $timesArrayB = $heats = $finalsA = $finalsB = [
			'',
			''
		];

		$timesArrayA[] = 'PB';
		$timesArrayB[] = date("Y") . ' PB';
		$heats[] = 'Heats';
		$finalsA[] = 'Semi-Finals';
		$finalsB[] = 'Finals';

		for ($i = 0; $i < sizeof($swimsArray); $i++) {
			if ($row[$swimsArray[$i]] == 1) {
				$swims[] = $swimsTextArray[$i];
				$timesArrayA[] = '';
				$timesArrayB[] = '';
			}
		}

		fputcsv($output, $swims);
		fputcsv($output, $timesArrayA);
		fputcsv($output, $timesArrayB);
		fputcsv($output, $heats);
		fputcsv($output, $finalsA);
		fputcsv($output, $finalsB);
	} while ($row = $sql->fetch(PDO::FETCH_ASSOC));
}
