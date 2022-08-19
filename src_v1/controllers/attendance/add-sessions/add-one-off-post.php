<?php

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

if (!$user->hasPermissions(['Admin', 'Committee', 'Coach'])) {
  halt(404);
}

if (!\SCDS\CSRF::verify()) {
  http_response_code(302);
  $_SESSION['TENANT-' . app()->tenant->getId()]['AddSessionError'] = 'We could not validate your CSRF token. Please try again.';
  header('location: ' . autoUrl('attendance/sessions/new'));
  return;
}

$sessionName = $venue = $startDate = $endDate = $startTime = $endTime = null;

if (isset($_POST['session-name']) && mb_strlen(trim($_POST['session-name'])) > 0) {
  $sessionName = mb_ucfirst(trim($_POST['session-name']));
}

if (isset($_POST['session-venue'])) {
  $validateVenue = $db->prepare("SELECT VenueID FROM sessionsVenues WHERE VenueID = ? AND Tenant = ?");
  $validateVenue->execute([
    $_POST['session-venue'],
    $tenant->getId(),
  ]);

  $venue = $validateVenue->fetchColumn();
}

if (isset($_POST['session-date'])) {
  try {
    $startDate = new DateTime($_POST['session-date'], new DateTimeZone('Europe/London'));

    $endDate = clone $startDate;
  } catch (Exception $e) {
  }
}

if (isset($_POST['recurring']) && $_POST['recurring'] == 'recurring') {
  if (isset($_POST['session-end-date'])) {
    try {
      $endDate = new DateTime($_POST['session-end-date'], new DateTimeZone('Europe/London'));
    } catch (Exception $e) {
      throw new Exception('End date invalid');
    }
  } else {
    throw new Exception('No end date provided');
  }
}

if (isset($_POST['session-start-time'])) {
  try {
    $startTime = DateTime::createFromFormat('H:i', $_POST['session-start-time'], new DateTimeZOne('Europe/London'));
  } catch (Exception $e) {
  }
}

if (isset($_POST['session-end-time'])) {
  try {
    $endTime = DateTime::createFromFormat('H:i', $_POST['session-end-time'], new DateTimeZOne('Europe/London'));
  } catch (Exception $e) {
  }
}

$error = false;
$errors = 'The following were not provided: ';
if ($sessionName == null) {
  $error = true;
  $errors .= 'Session Name ';
}
if ($venue == null) {
  $error = true;
  $errors .= 'Venue ';
}
if ($startDate == null) {
  $error = true;
  $errors .= 'Start Date ';
}
if ($endDate == null) {
  $error = true;
  $errors .= 'End Date ';
}
if ($startTime == null) {
  $error = true;
  $errors .= 'Start Time ';
}
if ($endTime == null) {
  $error = true;
  $errors .= 'End Time ';
}

if ($error) {
  http_response_code(302);
  $_SESSION['TENANT-' . app()->tenant->getId()]['AddSessionError'] = $errors;
  header('location: ' . autoUrl('attendance/sessions/new'));
  return;
}

// Add to database
$add = $db->prepare("INSERT INTO `sessions` (`VenueID`, `SessionName`, `SessionDay`, `StartTime`, `EndTime`, `DisplayFrom`, `DisplayUntil`, `Tenant`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$add->execute([
  $venue,
  $sessionName,
  $startDate->format('w'),
  $startTime->format('H:i'),
  $endTime->format('H:i'),
  $startDate->format('Y-m-d'),
  $endDate->format('Y-m-d'),
  $tenant->getId(),
]);

// Get id
$sessionId = $db->lastInsertId();

// Add squads if selected
$getSquads = $db->prepare("SELECT SquadID FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, SquadName ASC");
$getSquads->execute([
  $tenant->getId()
]);

$addSquadRecord = $db->prepare("INSERT INTO sessionsSquads (`Squad`, `Session`, `ForAllMembers`) VALUES (?, ?, ?)");
$hasSquad = false;

while ($squad = $getSquads->fetchColumn()) {
  if (isset($_POST['squad-' . $squad]) && bool($_POST['squad-' . $squad])) {
    $addSquadRecord->execute([
      $squad,
      $sessionId,
      (int) true,
    ]);
    $hasSquad = true;
  }
}

if (!$hasSquad || (isset($_POST['go-to-booking-settings']) && bool($_POST['go-to-booking-settings']))) {
  // Begin setup as a booking session
  http_response_code(302);
  header('location: ' . autoUrl('sessions/booking/book?session=' . urlencode($sessionId) . '&date=' . urlencode($startDate->format('Y-m-d'))));
} else {
  // Redirect to timetable page
  http_response_code(302);
  header('location: ' . autoUrl('sessions?year=' . urlencode($startDate->format('Y')) . '&week=' . urlencode($startDate->format('W')) . '#session-unique-id-' . $startDate->format('Y-m-d') . '-S' . $sessionId));
}
