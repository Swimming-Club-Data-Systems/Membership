<?php

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

if (!$user->hasPermissions(['Admin', 'Committee', 'Coach'])) {
  halt(404);
}

$responseData = [
  'post_data' => $_POST,
  'errors' => null,
  'status' => false,
  'redirect' => null,
];

$recurring = false;

header('content-type: application/json');

$db->beginTransaction();

try {

  if (!\SCDS\CSRF::verify()) {
    throw new Exception('We could not validate your CSRF token. Please try again.');
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
        $recurring = true;
      } catch (Exception $e) {
        throw new Exception('End date invalid');
      }
    } else {
      throw new Exception('No end date provided');
    }
  }

  if (isset($_POST['session-start-time'])) {
    $startTime = DateTime::createFromFormat('H:i', $_POST['session-start-time'], new DateTimeZone('Europe/London'));
  }

  if (isset($_POST['session-end-time'])) {
    $endTime = DateTime::createFromFormat('H:i', $_POST['session-end-time'], new DateTimeZone('Europe/London'));
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
    throw new Exception($errors);
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
        (int) (isset($_POST['main-sequence']) && $_POST['main-sequence'] == 'all'),
      ]);
      $hasSquad = true;
    }
  }

  if ($recurring && !$hasSquad) {
    throw new Exception('Alone and repeating session!');
  }

  if ((isset($_POST['recurring']) && $_POST['recurring'] == 'one-off') && (!$hasSquad || (isset($_POST['require-booking']) && bool($_POST['require-booking'])))) {
    // Begin setup as a booking session
    $responseData['redirect'] = autoUrl('sessions/booking/book?session=' . urlencode($sessionId) . '&date=' . urlencode($startDate->format('Y-m-d')));
  } else if (isset($_POST['recurring']) && $_POST['recurring'] != 'one-off' && !$hasSquad) {
    throw new Exception('No squads provided');
  }

  $db->commit();
  $responseData['status'] = true;
} catch (Exception $e) {
  $db->rollBack();
  $responseData['errors'] = $e->getMessage();
  reportError($e);
}

echo json_encode($responseData);
