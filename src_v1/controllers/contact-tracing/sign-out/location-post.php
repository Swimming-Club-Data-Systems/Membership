<?php

use GuzzleHttp\Client;

$db = app()->db;
$tenant = app()->tenant;

// Check if authenticated
$getRepCount = $db->prepare("SELECT COUNT(*) FROM squadReps WHERE User = ?");
$getRepCount->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
]);
$showSignOut = $getRepCount->fetchColumn() > 0;

$user = app()->user;
if ($user->hasPermission('Admin') || $user->hasPermission('Coach') || $user->hasPermission('Galas')) {
  $showSignOut = true;
}
if (!$showSignOut) {
  halt(404);
}

try {

  // Get room
  $getRoom = $db->prepare("SELECT `Location` FROM covidVisitors INNER JOIN covidLocations ON covidLocations.ID = covidVisitors.Location WHERE covidVisitors.ID = ? AND covidLocations.Tenant = ?");
  $getRoom->execute([
    $_POST['id'],
    $tenant->getId(),
  ]);

  $room = $getRoom->fetchColumn();

  if (!$room) {
    halt(404);
  }

  // Update data in db
  $setSignedOut = $db->prepare("UPDATE covidVisitors SET SignedOut = ? WHERE ID = ?");
  $setSignedOut->execute([
    (int) bool($_POST['state']),
    $_POST['id'],
  ]);

  // Prep JSON body for socket service
  $data = [
    'room' => 'covid_room:' . $room,
    'field' => $_POST['id'],
    'state' => bool($_POST['state']),
  ];

  $url = 'https://production-apis.tenant-services.membership.myswimmingclub.uk/covid/send-change-message';
  if (bool(getenv("IS_DEV"))) {
    $url = 'https://apis.tenant-services.membership.myswimmingclub.uk/covid/send-change-message';
  }

  http_response_code(200);

  try {

    $client = new Client();

    $r = $client->request('POST', $url, [
      'json' => $data
    ]);

  } catch (Exception $e) {
    // Ignore
  }
} catch (Exception $e) {

  http_response_code(500);

}
