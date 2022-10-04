<?php

use function GuzzleHttp\json_decode;

$db = app()->db;
$tenant = app()->tenant;

try {
  if (!isset($_GET['location']) || !isset($_GET['from-date']) || !isset($_GET['from-time']) || !isset($_GET['to-date']) || !isset($_GET['to-time'])) {
    throw new Exception();
  }

  $fromDate = DateTime::createFromFormat("Y-m-d H:i", $_GET['from-date'] . ' ' . $_GET['from-time'], new DateTimeZone('Europe/London'));
  $toDate = DateTime::createFromFormat("Y-m-d H:i", $_GET['to-date'] . ' ' . $_GET['to-time'], new DateTimeZone('Europe/London'));
  $fromDate->setTimezone(new DateTimeZone('UTC'));
  $toDate->setTimezone(new DateTimeZone('UTC'));

  $getLocation = $db->prepare("SELECT `ID`, `Name`, `Address` FROM covidLocations WHERE `ID` = ? AND `Tenant` = ?");
  $getLocation->execute([
    $_GET['location'],
    $tenant->getId()
  ]);
  $location = $getLocation->fetch(PDO::FETCH_ASSOC);

  if (!$location) {
    throw new Exception($e);
  }

  $getVisitors = $db->prepare("SELECT `ID`, `GuestName`, `GuestPhone`, `Time`, `Type`, `Person` FROM `covidVisitors` WHERE `Location` = ? AND `Time` >= ? AND `Time` <= ?");
  $getVisitors->execute([
    $_GET['location'],
    $fromDate->format('Y-m-d H:i:s'),
    $toDate->format('Y-m-d H:i:s'),
  ]);

  $visitors = [];

  while ($v = $getVisitors->fetch(PDO::FETCH_ASSOC)) {

    $user = $member = null;
    if ($v['Type'] == 'member') {
      $member = $v['Person'];
    } else if ($v['Type'] == 'user') {
      $user = $v['Person'];
    }

    $visitors[] = [
      'id' => $v['ID'],
      'name' => $v['GuestName'],
      'phone' => $v['GuestPhone'],
      'time' => $v['Time'],
      'user' => $user,
      'member' => $member,
      'type' => $v['Type'],
    ];
  }

  $data = [
    'location' => [
      'id' => $location['ID'],
      'name' => $location['Name'],
      'address' => json_decode($location['Address']),
    ],
    'visitors' => $visitors,
    'from' => $fromDate->format('c'),
    'to' => $toDate->format('c'),
  ];

  $json = json_encode($data, JSON_PRETTY_PRINT);

  if ($_GET['format'] == 'csv') {
    include 'csv-render.php';
  } else if ($_GET['format'] == 'html') {
    include 'htms-render.php';
  } else {
    header('content-type: application/json');
    echo $json;
  }

} catch (Exception $e) {
  // pre($e);
  http_response_code(302);
  header('location: ' . autoUrl('contact-tracing/reports'));
}
