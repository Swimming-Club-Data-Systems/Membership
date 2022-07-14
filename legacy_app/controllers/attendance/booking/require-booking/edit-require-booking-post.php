<?php

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

if (!$user->hasPermission('Admin') && !$user->hasPermission('Coach')) {
  halt(404);
}

try {

  if (!isset($_POST['session']) && !isset($_POST['date'])) throw new Exception('Missing POST data');

  $date = new DateTime($_POST['date'], new DateTimeZone('Europe/London'));

  // Get session
  $getSession = $db->prepare("SELECT `SessionID`, `SessionName`, `DisplayFrom`, `DisplayUntil`, `StartTime`, `EndTime`, `VenueName`, `Location`, `SessionDay`, `MaxPlaces`, `AllSquads`, `RegisterGenerated` FROM `sessionsBookable` INNER JOIN `sessions` ON sessionsBookable.Session = sessions.SessionID INNER JOIN `sessionsVenues` ON `sessions`.`VenueID` = `sessionsVenues`.`VenueID` WHERE `sessionsBookable`.`Session` = ? AND `sessionsBookable`.`Date` = ? AND `sessions`.`Tenant` = ? AND DisplayFrom <= ? AND DisplayUntil >= ?");
  $getSession->execute([
    $_POST['session'],
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
    throw new Exception('Date invalid for this session');
  }

  $sessionDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $date->format('Y-m-d') .  '-' . $session['StartTime'], new DateTimeZone('Europe/London'));
  $bookingCloses = clone $sessionDateTime;
  $bookingCloses->modify('-15 minutes');

  $now = new DateTime('now', new DateTimeZone('Europe/London'));

  $bookingClosed = $now > $bookingCloses || bool($session['RegisterGenerated']);

  $db->beginTransaction();

  try {

    if ($bookingClosed) {
      throw new Exception('Booking has closed - No changes can be mades');
    }

    $updateBookable = $db->prepare("UPDATE `sessionsBookable` SET `MaxPlaces` = ?, `AllSquads` = ?, `BookingOpens` = ?, `BookingFee` = ? WHERE `Session` = ? AND `Date` = ?");

    $maxPlaces = null;
    if (isset($_POST['number-limit']) && bool($_POST['number-limit']) && ((int) $_POST['max-count']) > 0) {
      $maxPlaces = ((int) $_POST['max-count']);
    }

    $allSquads = false;
    if (isset($_POST['open-to']) && bool($_POST['open-to'])) {
      $allSquads = true;
    }

    // Booking opening time
    $bookingOpensAt = null;
    if (isset($_POST['open-bookings']) && bool($_POST['open-bookings'])) {
      try {
        $bookingOpens = DateTime::createFromFormat('Y-m-d-H:i', $_POST['open-booking-at-date'] . '-' . $_POST['open-booking-at-time'], new DateTimeZone('Europe/London'));
        $bookingOpens->setTimezone(new DateTimeZone('UTC'));

        if ($bookingOpens > $now && $bookingOpens < $bookingCloses) {
          $bookingOpensAt = $bookingOpens->format('Y-m-d H:i:s');
        }
      } catch (Exception $e) {
        $bookingOpensAt = null;
      }
    }

    $bookingFee = 0;
    if (isset($_POST['booking-fees']) && bool($_POST['booking-fees'])) {
      $bookingFee = 0;
      try {
        $amountDec = \Brick\Math\BigDecimal::of((string) $_POST['booking-fees-amount']);
        $bookingFee = $amountDec->withPointMovedRight(2)->toInt();
      } catch (Exception $e) {
        $bookingFee = 0;
      }
    }

    $updateBookable->execute([
      $maxPlaces,
      (int) $allSquads,
      $bookingOpensAt,
      $bookingFee,
      $session['SessionID'],
      $date->format('Y-m-d'),
    ]);

    // Get overflow of places
    // Get number of bookings
    // Number booked in
    $getBookedCount = $db->prepare("SELECT COUNT(*) FROM `sessionsBookings` WHERE `Session` = ? AND `Date` = ?");
    $getBookedCount->execute([
      $session['SessionID'],
      $date->format('Y-m-d'),
    ]);
    $bookedCount = $getBookedCount->fetchColumn();

    if ($maxPlaces && $bookedCount > $maxPlaces) {
      // Some need to be removed
      $overflow = $bookedCount - $maxPlaces;

      // Get last x from bookings
      $getLastSignups = $db->prepare("SELECT `Member` id, `MForename` `fn`, `MSurname` `sn`, `UserID` `uid` FROM `sessionsBookings` INNER JOIN `members` ON `members`.`MemberID` = `sessionsBookings`.`Member` WHERE `Session` = ? AND `Date` = ? ORDER BY `BookedAt` DESC LIMIT ?");
      $getLastSignups->bindParam(1, $session['SessionID'], PDO::PARAM_INT);
      $getLastSignups->bindParam(2, $date->format('Y-m-d'), PDO::PARAM_STR);
      $getLastSignups->bindParam(3, $overflow, PDO::PARAM_INT);
      $getLastSignups->execute();

      $getUserDetails = $db->prepare("SELECT `Forename`, `Surname`, `EmailAddress` FROM `users` WHERE `UserID` = ?");
      $deleteMemberBooking = $db->prepare("DELETE FROM `sessionsBookings` WHERE `Session` = ? AND `Date` = ? AND `Member` = ?");
      $sendEmail = $db->prepare("INSERT INTO `notify` (`UserID`, `Status`, `Subject`, `Message`, `ForceSend`, `EmailType`) VALUES (:user, 'Queued', :subject, :message, 1, 'SessionBookings')");

      while ($deleteMember = $getLastSignups->fetch(PDO::FETCH_ASSOC)) {

        // Delete
        $deleteMemberBooking->execute([
          $session['SessionID'],
          $date->format('Y-m-d'),
          $deleteMember['id'],
        ]);

        if ($deleteMember['uid']) {
          $getUserDetails->execute([$deleteMember['uid']]);
          $userDetails = $getUserDetails->fetch(PDO::FETCH_ASSOC);

          if ($userDetails) {
            // Send cancellation email

            $subject = 'Cancelled Session Booking - ' . $session['SessionName'] . ', ' . $sessionDateTime->format('H:i, j Y T');
            $username = $userDetails['Forename'] . ' ' . $userDetails['Surname'];
            $emailAddress = $userDetails['EmailAddress'];
            $content = '<p><strong>Because of a reduction in the number of spaces available for ' . htmlspecialchars($session['SessionName'] . ', ' . $sessionDateTime->format('H:i, j Y T')) . ', we have had to automatically cancel your booking.</strong></p>';

            $content .= '<p>Session booking details were as follows;</p>';

            $content .= '<dl>';

            $content .= '<dt>Member</dt><dd>' . htmlspecialchars($deleteMember['fn'] . ' ' . $deleteMember['sn']) . '</dd>';
            $content .= '<dt>Session</dt><dd>' . htmlspecialchars($session['SessionName']) . '</dd>';
            $content .= '<dt>Date and time</dt><dd>' . htmlspecialchars($sessionDateTime->format('H:i, l j F Y T')) . '</dd>';
            $content .= '<dt>Location</dt><dd>' . htmlspecialchars($session['VenueName']) . ', <em>' . htmlspecialchars($session['Location']) . '</em></dd>';
            $content .= '<dt>Session Unique ID</dt><dd>' . htmlspecialchars($sessionDateTime->format('Y-m-d')) . '-S' . htmlspecialchars($session['SessionID']) . '</dd>';

            $content .= '</dl>';

            $content .= '<p>This action was performed by ' . htmlspecialchars($user->getFullName()) . '.</p>';

            $content .= '<p>Please contact us if you think your booking has been cancelled by mistake.</p>';

            $sendEmail->execute([
              "user" => $deleteMember['uid'],
              "subject" => $subject,
              "message" => $content
            ]);
          }
        }
      }
    }

    $db->commit();

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

    $_SESSION['TENANT-' . app()->tenant->getId()]['EditRequireBookingSuccess'] = true;
    http_response_code(302);
    header("location: " . autoUrl('sessions/booking/edit?session=' . $session['SessionID'] . '&date=' . $date->format('Y-m-d')));
  } catch (Exception $e) {

    $db->rollBack();

    $message = $e->getMessage();
    if (get_class($e) == 'PDOException') {
      reportError($e);
      $message = 'A database error occurred';
    }

    $_SESSION['TENANT-' . app()->tenant->getId()]['RequireBookingError'] = $message;
    http_response_code(302);
    header("location: " . autoUrl('sessions/booking/edit?session=' . $session['SessionID'] . '&date=' . $date->format('Y-m-d')));
  }
} catch (Exception $e) {

  reportError($e);
  halt(404);
}
