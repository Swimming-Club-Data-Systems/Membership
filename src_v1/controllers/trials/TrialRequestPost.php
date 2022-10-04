<?php

$db = app()->db;
$tenant = app()->tenant;

$query = $db->prepare("SELECT COUNT(*) FROM joinSwimmers WHERE ID = ? AND Tenant = ?");
$query->execute([
  $request,
  $tenant->getId()
]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$query = $db->prepare("SELECT Hash, joinParents.First F, joinParents.Last L, joinSwimmers.First, joinSwimmers.Last, Email FROM joinSwimmers JOIN joinParents WHERE ID = ? AND joinParents.Tenant = ?");
$query->execute([
  $request,
  $tenant->getId()
]);

$detail = $query->fetch(PDO::FETCH_ASSOC);

$start = $_POST['trial-date'] . ' ' . $_POST['trial-start'] . ':00';
$end = $_POST['trial-date'] . ' ' . $_POST['trial-end'] . ':00';

$day = date("j F Y", strtotime($start));
$start_string = date("H:i", strtotime($start));
$end_string = date("H:i", strtotime($end));

$query = $db->prepare("UPDATE joinSwimmers SET TrialStart = ?, TrialEnd = ? WHERE ID = ? AND Tenant = ?");
$query->execute([
  $start,
  $end,
  $request,
  $tenant->getId()
]);

$email_parent = '<p>Hello ' . $detail['F'] . ' ' . $detail['L'] . '</p>
<p>This is a confirmation that the trial appointment for ' . $detail['First'] . ' ' . $detail['Last'] . ' has been scheduled for ' . $day . ' at ' . $start_string . ' - ' . $end_string . '.</p>
<p>You can <a href="' . autoUrl("services/request-a-trial/" . $detail['Hash'] . "/status") . '">check your trial request status on our website</a>.</p>
<p>Should you need to rearrange the appointment, please reply to this email.</p>';

notifySend(null, 'Your Trial Appointment', $email_parent, $detail['F'] . ' ' . $detail['L'], $detail['Email']);

$_SESSION['TENANT-' . app()->tenant->getId()]['TrialAppointmentUpdated'] = true;
header("Location: " . currentUrl());
