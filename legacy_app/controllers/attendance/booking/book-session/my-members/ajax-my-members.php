<?php

use GuzzleHttp\json_encode;

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

header('content-type: application/json');

$status = 200;
$html = null;
$error = null;
$stats = [
  'placesTotal' => [
    'int' => 0,
    'string' => "Zero",
  ],
  'placesBooked' => [
    'int' => 0,
    'string' => "Zero",
  ],
  'placesRemaining' => [
    'int' => 0,
    'string' => "Zero",
  ]
];

include 'my-members.php';

try {

  if (!isset($_GET['session']) && !isset($_GET['date'])) throw new Exception('Data missing');

  $date = null;
  try {
    $date = new DateTime($_GET['date'], new DateTimeZone('Europe/London'));
  } catch (Exception $e) {
    throw new Exception("No valid date provided");
  }

  // Get session
  $getSession = $db->prepare("SELECT `SessionID`, `SessionName`, `DisplayFrom`, `DisplayUntil`, `StartTime`, `EndTime`, `VenueName`, `Location`, `SessionDay`, `MaxPlaces`, `AllSquads`, `BookingOpens` FROM `sessionsBookable` INNER JOIN `sessions` ON sessionsBookable.Session = sessions.SessionID INNER JOIN `sessionsVenues` ON `sessions`.`VenueID` = `sessionsVenues`.`VenueID` WHERE `sessionsBookable`.`Session` = ? AND `sessionsBookable`.`Date` = ? AND `sessions`.`Tenant` = ? AND DisplayFrom <= ? AND DisplayUntil >= ?");
  $getSession->execute([
    $_GET['session'],
    $date->format('Y-m-d'),
    $tenant->getId(),
    $date->format('Y-m-d'),
    $date->format('Y-m-d'),
  ]);

  $session = $getSession->fetch(PDO::FETCH_ASSOC);

  if (!$session) {
    throw new Exception("No such session");
  }

  // Validate session happens on this day
  $dayOfWeek = $date->format('w');

  if ($session['SessionDay'] != $dayOfWeek) {
    throw new Exception("Invalid session date");
  }

  $numFormatter = new NumberFormatter("en-GB", NumberFormatter::SPELLOUT);

  // Number booked in
  $getBookedCount = $db->prepare("SELECT COUNT(*) FROM `sessionsBookings` WHERE `Session` = ? AND `Date` = ?");
  $getBookedCount->execute([
    $session['SessionID'],
    $date->format('Y-m-d'),
  ]);
  $bookedCount = $getBookedCount->fetchColumn();

  if ($session['MaxPlaces']) {
    $stats['placesTotal']['string'] = $numFormatter->format($session['MaxPlaces']);
    $stats['placesTotal']['int'] = (int) $session['MaxPlaces'];

    $stats['placesRemaining']['string'] = $numFormatter->format($session['MaxPlaces'] - $bookedCount);
    $stats['placesRemaining']['int'] = (int) ($session['MaxPlaces'] - $bookedCount);
  } else {
    $stats['placesAvailable'] = $stats['placesRemaining'] = null;
  }

  $stats['placesBooked']['string'] = $numFormatter->format($bookedCount);
  $stats['placesBooked']['int'] = (int) $bookedCount;

  try {
    ob_start();
    echo getMySessionBookingMembers($session, $date);
    $html = ob_get_clean();
  } catch (Exception $e) {
    $status = 500;
  }
} catch (Exception $e) {
  $status = 500;
  $error = $e->getMessage();

  reportError($e);

  if (get_class($e) == 'PDOException') {
    $error = 'A database error occurred';
  }
}

echo json_encode([
  'status' => $status,
  'html' => trim($html),
  'error' => $error,
  'stats' => $stats,
]);
