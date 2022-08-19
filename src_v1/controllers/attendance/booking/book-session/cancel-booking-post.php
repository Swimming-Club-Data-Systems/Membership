<?php

use function GuzzleHttp\json_encode;

if (!isset($_SERVER['HTTP_ACCEPT']) || $_SERVER['HTTP_ACCEPT'] != 'application/json') {
  halt(404);
}

$json = [
  'status' => 200,
  'error' => 'No errors',
];

// Check details for this session

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

try {

  if (!isset($_POST['member-id']) || !isset($_POST['session-id']) || !isset($_POST['session-date'])) throw new Exception('Missing form data');

  $date = null;
  try {
    $date = new DateTime($_POST['session-date'], new DateTimeZone('Europe/London'));
  } catch (Exception $e) {
    throw new Exception('Invalid date');
  }

  // Get session
  $getSession = $db->prepare("SELECT `SessionID`, `SessionName`, `DisplayFrom`, `DisplayUntil`, `StartTime`, `EndTime`, `VenueName`, `Location`, `SessionDay`, `MaxPlaces`, `AllSquads`, `RegisterGenerated`, `BookingFee` FROM `sessionsBookable` INNER JOIN `sessions` ON sessionsBookable.Session = sessions.SessionID INNER JOIN `sessionsVenues` ON `sessions`.`VenueID` = `sessionsVenues`.`VenueID` WHERE `sessionsBookable`.`Session` = ? AND `sessionsBookable`.`Date` = ? AND `sessions`.`Tenant` = ? AND DisplayFrom <= ? AND DisplayUntil >= ?");
  $getSession->execute([
    $_POST['session-id'],
    $date->format('Y-m-d'),
    $tenant->getId(),
    $date->format('Y-m-d'),
    $date->format('Y-m-d'),
  ]);

  $session = $getSession->fetch(PDO::FETCH_ASSOC);

  if (!$session) {
    throw new Exception('Session not found');
  }

  // Validate session happens on this day
  $dayOfWeek = $date->format('w');

  if ($session['SessionDay'] != $dayOfWeek) {
    throw new Exception('Session not found');
  }

  $sessionDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $date->format('Y-m-d') .  '-' . $session['StartTime'], new DateTimeZone('Europe/London'));

  $bookingCloses = clone $sessionDateTime;
  $bookingCloses->modify('-15 minutes');

  $now = new DateTime('now', new DateTimeZone('Europe/London'));

  $bookingClosed = $now > $bookingCloses || bool($session['RegisterGenerated']);

  if ($bookingClosed) {
    throw new Exception('Booking has closed');
  }

  $numFormatter = new NumberFormatter("en-GB", NumberFormatter::SPELLOUT);

  // Validate member exists and belongs to user

  $getMember = $db->prepare("SELECT MForename, MSurname, MemberID, UserID FROM members WHERE MemberID = ?");
  $getMember->execute([
    $_POST['member-id'],
  ]);

  $member = $getMember->fetch(PDO::FETCH_ASSOC);

  // If no member not found
  if (!$member) throw new Exception('Member not found');

  // If unauthorised not found
  if (!$user->hasPermission('Admin') && !$user->hasPermission('Coach') && $member['UserID'] != $user->getId()) throw new Exception('Member not found');

  // Check a booking exists
  $getBookingCount = $db->prepare("SELECT COUNT(*) FROM `sessionsBookings` WHERE `Session` = ? AND `Date` = ? AND `Member` = ?");
  $getBookingCount->execute([
    $session['SessionID'],
    $date->format('Y-m-d'),
    $member['MemberID'],
  ]);

  if ($getBookingCount->fetchColumn() == 0) {
    throw new Exception('Booking does not exist');
  }

  // Cancel booking
  $cancel = $db->prepare("DELETE FROM sessionsBookings WHERE `Session` = ? AND `Date` = ? AND `Member` = ?");
  $cancel->execute([
    $session['SessionID'],
    $date->format('Y-m-d'),
    $member['MemberID'],
  ]);

  $url = 'https://production-apis.tenant-services.membership.myswimmingclub.uk/attendance/send-booking-page-change-message';
  if (bool(getenv("IS_DEV"))) {
    $url = 'https://apis.tenant-services.membership.myswimmingclub.uk/attendance/send-booking-page-change-message';
  }

  try {

    $client = new \GuzzleHttp\Client([
      'timeout' => 1.0,
    ]);

    $r = $client->request('POST', $url, [
      'json' => [
        'room' => 'session_booking_room:' . $date->format('Y-m-d') . '-S' . $session['SessionID'],
        'update' => true,
      ]
    ]);
  } catch (Exception $e) {
    // Ignore
  }

  // Send an email to the user
  $getUser = $db->prepare("SELECT MForename fn, MSurname sn, MemberID id, Forename ufn, Surname usn, EmailAddress email FROM members INNER JOIN users ON users.UserID = members.UserID WHERE members.MemberID = ?");
  $getUser->execute([
    $member['MemberID'],
  ]);

  $emailUser = $getUser->fetch(PDO::FETCH_ASSOC);

  if ($emailUser) {

    try {

      $subject = 'Cancelled Session Booking - ' . $session['SessionName'] . ', ' . $sessionDateTime->format('H:i, j Y T');
      $username = $emailUser['ufn'] . ' ' . $emailUser['usn'];
      $emailAddress = $emailUser['email'];
      $content = '<p>Hello ' . htmlspecialchars($username) . ',</p>';
      $content .= '<p><strong>' . htmlspecialchars($user->getFullName()) . ' has <em>cancelled</em> the following session booking;</strong></p>';

      $content .= '<dl>';

      $content .= '<dt>Member</dt><dd>' . htmlspecialchars($emailUser['fn'] . ' ' . $emailUser['sn']) . '</dd>';
      $content .= '<dt>Session</dt><dd>' . htmlspecialchars($session['SessionName']) . '</dd>';
      $content .= '<dt>Date and time</dt><dd>' . htmlspecialchars($sessionDateTime->format('H:i, l j F Y T')) . '</dd>';
      $content .= '<dt>Location</dt><dd>' . htmlspecialchars($session['VenueName']) . ', <em>' . htmlspecialchars($session['Location']) . '</em></dd>';
      $content .= '<dt>Session Unique ID</dt><dd>' . htmlspecialchars($sessionDateTime->format('Y-m-d')) . '-S' . htmlspecialchars($session['SessionID']) . '</dd>';

      $content .= '</dl>';

      if ($session['BookingFee'] > 0) {
        $content .= '<p>This means we will no longer charge you &pound;' . htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $session['BookingFee']))->withPointMovedLeft(2)->toScale(2)) . ' for this session.</p>';
      }

      $content .= '<p>Please contact us if you think your booking has been cancelled by mistake.</p>';

      notifySend(null, $subject, $content, $username, $emailAddress);
    } catch (Exception $e) {
      // Ignore failed send
      reportError($e);
    }
  }
} catch (Exception $e) {

  // reportError($e);

  $message = $e->getMessage();
  if (get_class($e) == 'PDOException') {
    $message = 'A database error occurred';
  }

  $json['status'] = 500;
  $json['error'] = $message;
}

http_response_code(200);
header('content-type: application/json');
echo json_encode($json);
