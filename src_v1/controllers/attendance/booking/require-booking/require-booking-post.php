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
  $getSession = $db->prepare("SELECT `SessionID`, `SessionName`, `DisplayFrom`, `DisplayUntil`, `StartTime`, `EndTime`, `VenueName`, `Location`, `SessionDay` FROM `sessions` INNER JOIN `sessionsVenues` ON `sessions`.`VenueID` = `sessionsVenues`.`VenueID` WHERE `sessions`.`SessionID` = ? AND `sessions`.`Tenant` = ? AND DisplayFrom <= ? AND DisplayUntil >= ?");
  $getSession->execute([
    $_POST['session'],
    $tenant->getId(),
    $date->format('Y-m-d'),
    $date->format('Y-m-d'),
  ]);

  $session = $getSession->fetch(PDO::FETCH_ASSOC);

  if (!$session) {
    throw new Exception('Session does not exist');
  }

  // Validate session happens on this day
  $dayOfWeek = $date->format('w');

  if ($session['SessionDay'] != $dayOfWeek) {
    throw new Exception('Date invalid for this session');
  }

  $sessionDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $date->format('Y-m-d') .  '-' . $session['StartTime'], new DateTimeZone('Europe/London'));
  $bookingCloses = clone $sessionDateTime;
  $bookingCloses->modify('-15 minutes');

  try {

    $addBookable = $db->prepare("INSERT INTO `sessionsBookable` (`Session`, `Date`, `MaxPlaces`, `AllSquads`, `BookingOpens`, `BookingFee`) VALUES (?, ?, ?, ?, ?, ?)");

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

    $addBookable->execute([
      $session['SessionID'],
      $date->format('Y-m-d'),
      $maxPlaces,
      (int) $allSquads,
      $bookingOpensAt,
      $bookingFee,
    ]);

    // Ensure register is clear

    // $_SESSION['TENANT-' . app()->tenant->getId()]['RequireBookingSuccess'] = $message;
    http_response_code(302);
    header("location: " . autoUrl('sessions/booking/book?session=' . $session['SessionID'] . '&date=' . $date->format('Y-m-d')));
  } catch (Exception $e) {

    $message = $e->getMessage();
    if (get_class($e) == 'PDOException') {
      $message = 'A database error occurred';
    }

    $_SESSION['TENANT-' . app()->tenant->getId()]['RequireBookingError'] = $message;
    http_response_code(302);
    header("location: " . autoUrl('sessions/booking/book?session=' . $session['SessionID'] . '&date=' . $date->format('Y-m-d')));
  }
} catch (Exception $e) {

  reportError($e);
  halt(404);
}
