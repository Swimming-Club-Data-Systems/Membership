<?php

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

if (!isset($_GET['session']) && !isset($_GET['date'])) halt(404);

$date = null;
try {
  $date = new DateTime($_GET['date'], new DateTimeZone('Europe/London'));
} catch (Exception $e) {
  halt(404);
}

// Get session
$getSession = $db->prepare("SELECT `SessionID`, `SessionName`, `DisplayFrom`, `DisplayUntil`, `StartTime`, `EndTime`, `VenueName`, `Location`, `SessionDay`, `MaxPlaces`, `AllSquads`, `RegisterGenerated`, `BookingOpens`, `BookingFee` FROM `sessionsBookable` INNER JOIN `sessions` ON sessionsBookable.Session = sessions.SessionID INNER JOIN `sessionsVenues` ON `sessions`.`VenueID` = `sessionsVenues`.`VenueID` WHERE `sessionsBookable`.`Session` = ? AND `sessionsBookable`.`Date` = ? AND `sessions`.`Tenant` = ? AND DisplayFrom <= ? AND DisplayUntil >= ?");
$getSession->execute([
  $_GET['session'],
  $date->format('Y-m-d'),
  $tenant->getId(),
  $date->format('Y-m-d'),
  $date->format('Y-m-d'),
]);

$session = $getSession->fetch(PDO::FETCH_ASSOC);

if (!$session) {
  halt(404);
}

// Validate session happens on this day
$dayOfWeek = $date->format('w');

if ($session['SessionDay'] != $dayOfWeek) {
  halt(404);
}

$numFormatter = new NumberFormatter("en-GB", NumberFormatter::SPELLOUT);

// Number booked in
$getBookedCount = $db->prepare("SELECT COUNT(*) FROM `sessionsBookings` WHERE `Session` = ? AND `Date` = ?");
$getBookedCount->execute([
  $session['SessionID'],
  $date->format('Y-m-d'),
]);
$bookedCount = $getBookedCount->fetchColumn();

$sessionDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $date->format('Y-m-d') .  '-' . $session['StartTime'], new DateTimeZone('Europe/London'));
$startTime = new DateTime($session['StartTime'], new DateTimeZone('UTC'));
$endTime = new DateTime($session['EndTime'], new DateTimeZone('UTC'));
$duration = $startTime->diff($endTime);
$hours = (int) $duration->format('%h');
$mins = (int) $duration->format('%i');

$sessionDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $date->format('Y-m-d') .  '-' . $session['StartTime'], new DateTimeZone('Europe/London'));
$sessionEndDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $date->format('Y-m-d') .  '-' . $session['EndTime'], new DateTimeZone('Europe/London'));

$bookingCloses = clone $sessionDateTime;
$bookingCloses->modify('-15 minutes');

$now = new DateTime('now', new DateTimeZone('Europe/London'));
$bookingTime = new DateTime('now', new DateTimeZone('UTC'));

$bookingClosed = $now > $bookingCloses || bool($session['RegisterGenerated']);

$getCoaches = $db->prepare("SELECT Forename fn, Surname sn, coaches.Type code FROM coaches INNER JOIN users ON coaches.User = users.UserID WHERE coaches.Squad = ? ORDER BY coaches.Type ASC, Forename ASC, Surname ASC");

$getSessionSquads = $db->prepare("SELECT SquadName, ForAllMembers, SquadID FROM `sessionsSquads` INNER JOIN `squads` ON sessionsSquads.Squad = squads.SquadID WHERE sessionsSquads.Session = ? ORDER BY SquadFee DESC, SquadName ASC;");
$getSessionSquads->execute([
  $session['SessionID'],
]);
$squadNames = $getSessionSquads->fetchAll(PDO::FETCH_ASSOC);

$theTitle = 'Book ' . $session['SessionName'] . ' at ' . $startTime->format('H:i') . ' on ' . $date->format('j F Y') . ' - ' . $tenant->getName();
$theLink = autoUrl('sessions/booking/book?session=' . urlencode($session['SessionID']) . '&date=' . urlencode($date->format('Y-m-d')));

// Get session squads
$getSquads = $db->prepare("SELECT `Squad` FROM `sessionsSquads` WHERE `Session` = ?");
$getSquads->execute([
  $session['SessionID'],
]);

while ($squad = $getSquads->fetchColumn()) {
  // Get members for this user in this squad
  $getMembers = $db->prepare("SELECT MForename fn, MSurname sn, MemberID id FROM members INNER JOIN squadMembers ON members.MemberID = squadMembers.Member WHERE squadMembers.Squad = ? ORDER BY fn ASC, sn ASC");
  $getMembers->execute([
    $squad,
  ]);

  while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)) {
    $members[] = $member;
  }
}

// Remove duplicated
// $members = array_unique($members);
$members = array_map("unserialize", array_unique(array_map("serialize", $members)));

$getBooking = $db->prepare("SELECT `BookedAt` FROM `sessionsBookings` WHERE `Session` = ? AND `Date` = ? AND `Member` = ?");

$sendEmail = $db->prepare("INSERT INTO `notify` (`UserID`, `Status`, `Subject`, `Message`, `ForceSend`, `EmailType`) VALUES (:user, 'Queued', :subject, :message, 1, 'SessionBookings')");

if (!$bookingClosed) {
  foreach ($members as $member) {
    $getBooking->execute([
      $session['SessionID'],
      $date->format('Y-m-d'),
      $member['id'],
    ]);
    $booking = $getBooking->fetchColumn();

    try {

      if (!$booking && isset($_POST['member-checkbox-' . $member['id']])) {
        // Add a booking
        // Number booked in
        $getBookedCount = $db->prepare("SELECT COUNT(*) FROM `sessionsBookings` WHERE `Session` = ? AND `Date` = ?");
        $getBookedCount->execute([
          $session['SessionID'],
          $date->format('Y-m-d'),
        ]);
        $bookedCount = $getBookedCount->fetchColumn();

        // Max number
        $maxNumber = PHP_INT_MAX;
        if ($session['MaxPlaces']) {
          $maxNumber = $session['MaxPlaces'];
        }

        $placesAvailable = $maxNumber - $bookedCount;

        if ($placesAvailable == 0) {
          $bookingPossible = false;
          throw new Exception('No spaces available to book');
        }

        // Check not already booked

        $getBookingCount = $db->prepare("SELECT COUNT(*) FROM `sessionsBookings` WHERE `Session` = ? AND `Date` = ? AND `Member` = ?");
        $getBookingCount->execute([
          $session['SessionID'],
          $date->format('Y-m-d'),
          $member['id'],
        ]);

        if ($getBookingCount->fetchColumn() > 0) {
          throw new Exception($member['fn'] . ' is already booked onto this session');
        }

        // Book a space for the member

        $addToBookings = $db->prepare("INSERT INTO `sessionsBookings` (`Session`, `Date`, `Member`, `BookedAt`) VALUES (?, ?, ?, ?)");
        $addToBookings->execute([
          $session['SessionID'],
          $date->format('Y-m-d'),
          $member['id'],
          $bookingTime->format('Y-m-d H:i:s'),
        ]);

        $duration = $sessionDateTime->diff($sessionEndDateTime);
        $hours = (int) $duration->format('%h');
        $mins = (int) $duration->format('%i');

        $getCoaches = $db->prepare("SELECT Forename fn, Surname sn, coaches.Type code FROM coaches INNER JOIN users ON coaches.User = users.UserID WHERE coaches.Squad = ? ORDER BY coaches.Type ASC, Forename ASC, Surname ASC");

        $getSessionSquads = $db->prepare("SELECT SquadName, ForAllMembers, SquadID FROM `sessionsSquads` INNER JOIN `squads` ON sessionsSquads.Squad = squads.SquadID WHERE sessionsSquads.Session = ? ORDER BY SquadFee DESC, SquadName ASC;");
        $getSessionSquads->execute([
          $session['SessionID'],
        ]);
        $squadNames = $getSessionSquads->fetchAll(PDO::FETCH_ASSOC);

        // Generate a confirmation email
        // Get member / user details

        $getUser = $db->prepare("SELECT MForename fn, MSurname sn, MemberID id, Forename ufn, Surname usn, EmailAddress email, users.UserID `uid` FROM members INNER JOIN users ON users.UserID = members.UserID WHERE members.MemberID = ?");
        $getUser->execute([
          $member['id'],
        ]);

        $emailUser = $getUser->fetch(PDO::FETCH_ASSOC);

        if ($emailUser) {

          try {

            $subject = 'Session Booking Confirmation - ' . $session['SessionName'] . ', ' . $sessionDateTime->format('H:i, j Y T');
            $username = $emailUser['ufn'] . ' ' . $emailUser['usn'];
            $emailAddress = $emailUser['email'];
            $content = '<p>This is confirmation of the following session booking;</p>';

            $content .= '<dl>';

            $content .= '<dt>Member</dt><dd>' . htmlspecialchars($emailUser['fn'] . ' ' . $emailUser['sn']) . '</dd>';
            $content .= '<dt>Session</dt><dd>' . htmlspecialchars($session['SessionName']) . '</dd>';
            $content .= '<dt>Date and time</dt><dd>' . htmlspecialchars($sessionDateTime->format('H:i, l j F Y T')) . '</dd>';
            $content .= '<dt>End time</dt><dd>' . htmlspecialchars($sessionEndDateTime->format('H:i')) . '</dd>';

            // Duration string
            $durationString = '';
            if ($hours > 0) {
              $durationString .= $hours . ' hour';
              if ($hours > 1) {
                $durationString .= 's';
              }
            }
            if ($mins > 0) {
              $durationString .= $mins . ' minute';
              if ($mins > 1) {
                $durationString .= 's';
              }
            }

            $content .= '<dt>Duration</dt><dd>' . htmlspecialchars($durationString) . '</dd>';

            $content .= '<dt>Price</dt>';
            if ($session['BookingFee'] > 0) {
              $content .= '<dd>&pound;' . htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $session['BookingFee']))->withPointMovedLeft(2)->toScale(2)) . '</dd>';
            } else {
              $content .= '<dd>Free</dd>';
            }

            // Coaches
            // $content .= '<dt>Duration</dt><dd>' . htmlspecialchars($durationString) . '</dd>';
            for ($i = 0; $i < sizeof($squadNames); $i++) {
              $getCoaches->execute([
                $squadNames[$i]['SquadID'],
              ]);
              $coaches = $getCoaches->fetchAll(PDO::FETCH_ASSOC);

              $content .= '<dt>' . htmlspecialchars($squadNames[$i]['SquadName']) . ' Coach';

              if (sizeof($coaches) > 0) {
                $content .= 'es';
              }

              $content .= '</dt><dd><ul style="margin-top: 0px;margin-bottom: 0px;">';

              for ($y = 0; $y < sizeof($coaches); $y++) {
                $content .= '<li><strong>' . htmlspecialchars($coaches[$y]['fn'] . ' ' . $coaches[$y]['sn']) . '</strong>, ' . htmlspecialchars(coachTypeDescription($coaches[$y]['code'])) . '</li>';
              }
              if (sizeof($coaches) == 0) {
                $content .= '<li>None assigned</li>';
              }

              $content .= '</ul></dd>';
            }

            $content .= '<dt>Location</dt><dd>' . htmlspecialchars($session['VenueName']) . ', <em>' . htmlspecialchars($session['Location']) . '</em></dd>';
            $content .= '<dt>Session Unique ID</dt><dd>' . htmlspecialchars($sessionDateTime->format('Y-m-d')) . '-S' . htmlspecialchars($session['SessionID']) . '</dd>';

            $content .= '</dl>';

            $content .= '<p>Booking made on your behalf by ' . htmlspecialchars($user->getFullName()) . '.</p>';

            if ($session['BookingFee'] > 0) {
              $content .= '<p>We will apply the booking fee to your account when we generate the register for this session. This happens approximately fifteen minutes before the session start time. The fee is payable as part of your next direct debit payment.</p>';
            }

            $content .= '<p>Penalties may apply for non-attendance.</p>';
            $content .= '<p>If you need to cancel your booking, please contact the person running this session or a member of club staff as soon as possible.</p>';

            $sendEmail->execute([
              "user" => $emailUser['uid'],
              "subject" => $subject,
              "message" => $content
            ]);
          } catch (Exception $e) {
            // Ignore failed send
          }
        }

        $_SESSION['TENANT-' . app()->tenant->getId()]['BookOnBehalfOfSuccess'] = true;
      }
    } catch (Exception $e) {
      $_SESSION['TENANT-' . app()->tenant->getId()]['BookOnBehalfOfError'] = true;
    }
  }
}

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['BookOnBehalfOfSuccess'])) {
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
}

http_response_code(302);
header('location: ' . autoUrl('sessions/booking/book-on-behalf-of?session=' . urlencode($session['SessionID']) . '&date=' . urlencode($date->format('Y-m-d'))));
