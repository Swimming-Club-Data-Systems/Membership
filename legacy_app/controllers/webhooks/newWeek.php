<?php

$db = nezamy_app()->db;
$tenant = nezamy_app()->tenant;

function insertWeekStart($week) {
	$db = nezamy_app()->db;
	$tenant = nezamy_app()->tenant;
	
	$insert = $db->prepare("INSERT INTO sessionsWeek (WeekDateBeginning, Tenant) VALUES (?, ?)");
	$insert->execute([
		$week,
		$tenant->getId()
	]);
}

// Get the date of the week beginning
$day = date('w');
$week_start = date('Y-m-d', strtotime('-'.$day.' days'));

// See if the date exists
$getLatestWeek = $db->prepare("SELECT WeekDateBeginning FROM sessionsWeek WHERE Tenant = ? ORDER BY WeekDateBeginning DESC LIMIT 1");
$getLatestWeek->execute([
	$tenant->getId()
]);
$latestWeekDB = $getLatestWeek->fetchColumn();
if ($latestWeekDB != null) {
	date('Y-m-d', strtotime($latestWeekDB));
	if ($week_start != $latestWeekDB) {
		insertWeekStart($week_start);
	}
}
else {
	insertWeekStart($week_start);
}
