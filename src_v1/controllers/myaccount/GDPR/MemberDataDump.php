<?php

$db = app()->db;
$tenant = app()->tenant;

$verify_user = $db->prepare("SELECT UserID FROM members WHERE MemberID = ?");
$verify_user->execute([$id]);

$parent = $verify_user->fetchColumn();

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent" && $parent != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) {
  halt(404);
}

$parent_name = getUserName($parent);
$downloader_name = getUserName($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']);
$swimmer_name = getSwimmerName($id);

$acc_details = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile, EmailComms, MobileComms FROM users WHERE UserID = ?");
$acc_details->execute([$parent]);

$swimmers = $db->prepare("SELECT MForename, MMiddleNames, MSurname, DateOfBirth, Gender, members.ASANumber, members.NGBCategory FROM members INNER JOIN squadMembers ON squadMembers.Member = members.MemberID INNER JOIN squads ON squadMembers.Squad = squads.SquadID WHERE members.MemberID = ? ORDER BY MForename ASC, MSurname ASC");
$swimmers->execute([$id]);

$med = $db->prepare("SELECT Conditions, Allergies, Medication FROM memberMedical WHERE MemberID = ?");
$med->execute([$id]);

$photo = $db->prepare("SELECT Website, Social, Noticeboard, FilmTraining, ProPhoto FROM memberPhotography WHERE MemberID = ?");
$photo->execute([$id]);

$galas = $db->prepare("SELECT GalaName, GalaDate, 50Free, 100Free, 200Free, 400Free, 800Free, 1500Free, 50Breast, 100Breast, 200Breast, 50Fly, 100Fly, 200Fly, 50Back, 100Back, 200Back, 100IM, 200IM, 400IM FROM galaEntries INNER JOIN galas ON galas.GalaID = galaEntries.GalaID WHERE MemberID = ? ORDER BY GalaDate DESC, GalaName ASC");
$galas->execute([$id]);

$emergency_contacts = $db->prepare("SELECT Name, ContactNumber FROM emergencyContacts WHERE UserID = ?");
$emergency_contacts->execute([$parent]);

// Get the last four weeks to calculate attendance
$weeks = 20; // Number of weeks data to return
$latestWeek = $db->query("SELECT MAX(WeekID) FROM sessionsWeek")->fetchColumn();
$startWeek = $latestWeek - $weeks;
if ($startWeek < 1) {
  $startWeek = 1;
}
$sessionData = $db->prepare("SELECT SessionName, SessionDay, WeekDateBeginning, AttendanceBoolean, StartTime FROM (sessionsAttendance INNER JOIN sessions ON sessions.SessionID = sessionsAttendance.SessionID) INNER JOIN sessionsWeek ON sessionsAttendance.WeekID = sessionsWeek.WeekID WHERE sessionsAttendance.WeekID >= :week && MemberID = :member ORDER BY sessionsAttendance.WeekID DESC, SessionDay DESC");
$sessionData->execute(['week' => $startWeek, 'member' => $id]);

// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=GDpe-Data-Download-' . str_replace(' ', '-', $swimmer_name) . '.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

fputcsv($output, array(app()->tenant->getKey('CLUB_NAME') . ' Member Data Download'));
fputcsv($output, ['Data for ' . $swimmer_name]);
fputcsv($output, ['Member Parent is ' . $parent_name]);
fputcsv($output, ['Downloaded by ' . $downloader_name]);
fputcsv($output, array('Data downloaded at ' . date("H:i, d/m/Y")));
fputcsv($output, ['-----']);

fputcsv($output, array('Parent Account Details'));
fputcsv($output, array('Forename', 'Surname', 'Email Address', 'Phone', 'Email Allowed', 'SMS Allowed'));
$row = $acc_details->fetch(PDO::FETCH_NUM);
fputcsv($output, $row);
fputcsv($output, ['-----']);

fputcsv($output, array('Swimmer Details'));
fputcsv($output, array('First', 'Middle', 'Last', 'DOB', 'Squad', 'Sex', $tenant->getKey('NGB_NAME') . ' ID', $tenant->getKey('NGB_NAME') . ' Category (Internal Code)'));
$row = $swimmers->fetch(PDO::FETCH_NUM);
do {
  fputcsv($output, $row);
  $row = $swimmers->fetch(PDO::FETCH_NUM);
} while ($row != null);
fputcsv($output, ['-----']);
fputcsv($output, array('Medical Details'));
fputcsv($output, array('Conditions', 'Allergies', 'Medication'));
$row = $med->fetch(PDO::FETCH_NUM);
do {
  fputcsv($output, $row);
  $row = $med->fetch(PDO::FETCH_NUM);
} while ($row != null);
fputcsv($output, ['-----']);

fputcsv($output, array('Allow Photography for'));
fputcsv($output, array('Website', 'Social Media', 'Noticeboard', 'Filming for Training', 'Professional Photographer'));
$row = $photo->fetch(PDO::FETCH_NUM);
do {
  fputcsv($output, $row);
  $row = $photo->fetch(PDO::FETCH_NUM);
} while ($row != null);
fputcsv($output, ['No data if 18 or over']);
fputcsv($output, ['-----']);

fputcsv($output, array('Emergency Contacts'));
fputcsv($output, array('Name', 'Contact Number'));
$row = $emergency_contacts->fetch(PDO::FETCH_NUM);
do {
  fputcsv($output, $row);
  $row = $emergency_contacts->fetch(PDO::FETCH_NUM);
} while ($row != null);
fputcsv($output, ['-----']);

fputcsv($output, array('Gala Entries (All)'));
fputcsv($output, array('Name', 'Date', '50 Free', '100 Free', '200 Free', '400 Free', '800 Free', '1500 Free', '50 Breast', '100 Breast', '200 Breast', '50 Fly', '100 Fly', '200 Fly', '50 Back', '100 Back', '200 Back', '100 IM', '200 IM', '400 IM'));
$row = $galas->fetch(PDO::FETCH_NUM);
do {
  fputcsv($output, $row);
  $row = $galas->fetch(PDO::FETCH_NUM);
} while ($row != null);
fputcsv($output, ['Entry times are not personal data']);
fputcsv($output, ['-----']);

fputcsv($output, ['Swimmer Attendance']);
fputcsv($output, ['']);
$row = $sessionData->fetch(PDO::FETCH_NUM);
do {
  fputcsv($output, [$row[0], date("H:i", strtotime($row[4])), date('j F Y', strtotime($row[2] . ' + ' . $row[1] . ' days')), $row[3]]);
  $row = $sessionData->fetch(PDO::FETCH_NUM);
} while ($row != null);
fputcsv($output, ['Accuracy for attendance data is not guaranteed']);
fputcsv($output, ['Further records can be made available for child protection']);
fputcsv($output, ['-----']);

fputcsv($output, ['-- DOCUMENT GENERATED BY --']);
fputcsv($output, array(app()->tenant->getKey('CLUB_NAME') . ' GDPR Compliance Team', 'via Membership Software', 'by SCDS'));
