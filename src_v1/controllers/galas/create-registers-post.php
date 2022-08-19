<?php

$db = app()->db;
$tenant = app()->tenant;

$galaDetails = $db->prepare("SELECT GalaName `name`, GalaDate `ends` FROM galas WHERE GalaID = ? AND Tenant = ?");
$galaDetails->execute([
  $id,
  $tenant->getId()
]);
$gala = $galaDetails->fetch(PDO::FETCH_ASSOC);

if ($gala == null) {
  halt(404);
}

$galaDate = new DateTime($gala['ends'], new DateTimeZone('Europe/London'));
$nowDate = new DateTime('now', new DateTimeZone('Europe/London'));

$getSessions = $db->prepare("SELECT `Name`, `ID` FROM galaSessions WHERE Gala = ? ORDER BY `ID` ASC");
$getSessions->execute([$id]);

$venue = null;

// Get venue
if (isset($_POST['session-venue'])) {
  $validateVenue = $db->prepare("SELECT VenueID FROM sessionsVenues WHERE VenueID = ? AND Tenant = ?");
  $validateVenue->execute([
    $_POST['session-venue'],
    $tenant->getId(),
  ]);

  $venue = $validateVenue->fetchColumn();
}

$db->beginTransaction();

try {

  while ($session = $getSessions->fetch(PDO::FETCH_ASSOC)) {
    if (isset($_POST['session-date-' . $session['ID']]) && isset($_POST['session-start-' . $session['ID']])) {

      // Calculate start time
      $start = DateTime::createFromFormat('Y-m-d H:i', $_POST['session-date-' . $session['ID']] . ' ' . $_POST['session-start-' . $session['ID']], new DateTimeZone('Europe/London'));
      $end = clone $start;
      $end->add(new DateInterval('PT3H'));

      $sessionName = $gala['name'] . ' ' . $session['Name'];

      // Add to database
      $add = $db->prepare("INSERT INTO `sessions` (`VenueID`, `SessionName`, `SessionDay`, `StartTime`, `EndTime`, `DisplayFrom`, `DisplayUntil`, `Tenant`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
      $add->execute([
        $venue,
        $sessionName,
        $start->format('w'),
        $start->format('H:i'),
        $end->format('H:i'),
        $start->format('Y-m-d'),
        $end->format('Y-m-d'),
        $tenant->getId(),
      ]);

      $sessionId = $db->lastInsertId();

      // Create "Bookable" session
      $add = $db->prepare("INSERT INTO `sessionsBookable` (`Session`, `Date`, `MaxPlaces`, `AllSquads`, `RegisterGenerated`, `BookingOpens`, `BookingFee`) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $add->execute([
        $sessionId,
        $start->format('Y-m-d'),
        null,
        0,
        0,
        $start->format('Y-m-d H:i:s'),
        0
      ]);

      // Get members to add to registers
      $getMembers = $db->prepare("SELECT MemberID FROM galaEntries WHERE GalaID = ?");
      $getMembers->execute([
        $id,
      ]);

      // Add booking
      $addBooking = $db->prepare("INSERT INTO `sessionsBookings` (`Session`, `Date`, `Member`, `BookedAt`) VALUE (?, ?, ?, ?)");

      while ($member = $getMembers->fetchColumn()) {
        $addBooking->execute([
          $sessionId,
          $start->format('Y-m-d'),
          $member,
          $nowDate->format('Y-m-d H:i:s')
        ]);
      }
    }
  }

  $db->commit();

  $_SESSION['TENANT-' . app()->tenant->getId()]['CreateRegisterSuccessStatus'] = true;

  http_response_code(302);
  header("location: " . autoUrl("galas/$id"));

} catch (Exception $e) {
  $db->rollBack();

  $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStatus'] = true;

  http_response_code(302);
  header("location: " . autoUrl("galas/$id/create-registers"));
}