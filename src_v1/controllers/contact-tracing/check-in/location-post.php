<?php

pre($_POST);

use function GuzzleHttp\json_decode;
use Respect\Validation\Validator as v;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;

$db = app()->db;
$tenant = app()->tenant;

$getLocation = $db->prepare("SELECT `ID`, `Name`, `Address` FROM covidLocations WHERE `ID` = ? AND `Tenant` = ?");
$getLocation->execute([
  $id,
  $tenant->getId()
]);
$location = $getLocation->fetch(PDO::FETCH_ASSOC);

if (!$location) {
  halt(404);
}

function getUUID()
{
  $uuid = Ramsey\Uuid\Uuid::uuid4();
  return $uuid->toString();
}

$getGuests = $getMembers = null;
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) {
  $getGuests = $db->prepare("SELECT ID, GuestName, GuestPhone FROM covidVisitors WHERE Inputter = ?");
  $getGuests->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
  ]);
  $getMembers = $db->prepare("SELECT MForename fn, MSurname sn, MemberID `id` FROM members WHERE `UserID` = ? ORDER BY fn ASC, sn ASC");
  $getMembers->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
  ]);
}

$addRecord = $db->prepare("INSERT INTO covidVisitors (`ID`, `Location`, `Time`, `Person`, `Type`, `GuestName`, `GuestPhone`, `Inputter`, `Tenant`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);");

$time = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');

$db->beginTransaction();

try {

  if (!SCDS\CSRF::verify()) {
    throw new Exception('Cross Site Request Forgery Verification failed');
  }

  // Validate guests first
  $guests = [];

  // Check fields exist and are all the same size
  if (isset($_POST['guest_name']) && isset($_POST['guest_phone']) && isset($_POST['guest_entry_uuid']) && sizeof($_POST['guest_name']) == sizeof($_POST['guest_phone']) && sizeof($_POST['guest_phone']) == sizeof($_POST['guest_entry_uuid'])) {

    for ($i = 0; $i < sizeof($_POST['guest_name']); $i++) {

      // Go ahead if form filled out, otherwise ignore it
      if (mb_strlen($_POST['guest_phone'][$i]) > 0 && mb_strlen($_POST['guest_name'][$i]) > 0) {

        $phone = null;
        try {
          $numberParser = PhoneNumber::parse($_POST['guest_phone'][$i], 'GB');
          $phone = $numberParser->format(PhoneNumberFormat::E164);
        } catch (PhoneNumberParseException $e) {
          throw new Exception('Invalid phone number');
        } catch (Exception $e) {
          throw new Exception('Invalid phone number');
        }

        if (!(mb_strlen($_POST['guest_name'][$i])) > 0) {
          throw new Exception('Missing guest name');
        }

        $guests[] = [
          'name' => mb_strimwidth($_POST['guest_name'][$i], 0, 256),
          'phone' => $phone,
        ];
      }
    }
  } else if (isset($_POST['guest_name']) || isset($_POST['guest_phone']) || isset($_POST['guest_entry_uuid'])) {
    // Accept or none, not some
    throw new Exception('A required field was missing');
  }

  $userMobile = null;

  // Add user if logged in
  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) {

    // Get user name and phone number
    $getUser = $db->prepare("SELECT Forename, Surname, Mobile FROM users WHERE UserID = ?");
    $getUser->execute([
      $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
    ]);

    $user = $getUser->fetch(PDO::FETCH_ASSOC);

    if ($user && isset($_POST['user']) && bool($_POST['user'])) {
      $addRecord->execute([
        getUUID(),
        $id,
        $time,
        $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
        'user',
        $user['Forename'] . ' ' . $user['Surname'],
        $user['Mobile'],
        null,
        $tenant->getId(),
      ]);
    }

    $userMobile = $user['Mobile'];
  }

  // Add existing guests
  if ($getGuests) {
    while ($guest = $getGuests->fetch(PDO::FETCH_ASSOC)) {
      if (isset($_POST['guest-' . $guest['ID']]) && bool($_POST['guest-' . $guest['ID']])) {
        $addRecord->execute([
          getUUID(),
          $id,
          $time,
          null,
          'guest',
          $guest['GuestName'],
          $guest['GuestPhone'],
          null,
          $tenant->getId(),
        ]);
      }
    }
  }

  // Add members
  if ($getMembers) {
    while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)) {
      if (isset($_POST['member-' . $member['id']]) && bool($_POST['member-' . $member['id']])) {
        $addRecord->execute([
          getUUID(),
          $id,
          $time,
          $member['id'],
          'member',
          $member['fn'] . ' ' . $member['sn'],
          $userMobile,
          null,
          $tenant->getId(),
        ]);
      }
    }
  }

  // Add new guest users
  foreach ($guests as $guest) {
    $addRecord->execute([
      getUUID(),
      $id,
      $time,
      null,
      'guest',
      $guest['name'],
      $guest['phone'],
      $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
      $tenant->getId(),
    ]);
  }

  $db->commit();

  $_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingSuccess'] = true;
  header("location: " . autoUrl('contact-tracing/check-in/' . $id . '/success'));
} catch (PDOException $e) {
  throw new Exception('A database error occurred');
} catch (Exception $e) {
  $db->rollBack();
  $_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingError'] = [
    'post' => $_POST,
    'message' => $e->getMessage(),
  ];
  header('location: ' . autoUrl('contact-tracing/check-in/' . $id));
}
