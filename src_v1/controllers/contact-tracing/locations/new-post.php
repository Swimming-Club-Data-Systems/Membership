<?php

use function GuzzleHttp\json_encode;
use Brick\Postcode\PostcodeFormatter;

$PostcodeFormatter = new PostcodeFormatter();

$tenant = app()->tenant;
$db = app()->db;

try {
  if (!SCDS\CSRF::verify()) {
    throw new Exception('Invalid CSRF Token');
  }

  $streetAndNumber = $flatOrBuilding = $city = $postCode = null;

  if (!isset($_POST['location-name']) && mb_strlen((string) $_POST['location-name']) < 1) {
    throw new Exception('You must provide a name for this location');
  } else {
    $streetAndNumber = $_POST['location_name'];
  }

  if (!isset($_POST['street-and-number']) && mb_strlen((string) $_POST['street-and-number']) < 1) {
    throw new Exception('You must provide a street name and building name or number');
  } else {
    $streetAndNumber = $_POST['street-and-number'];
  }

  if (isset($_POST['flat-building']) && mb_strlen((string) $_POST['flat-building']) > 1) {
    $flatOrBuilding = $_POST['flat-building'];
  }

  if (!isset($_POST['town-city']) && mb_strlen((string) $_POST['town-city']) < 1) {
    throw new Exception('You must provide a town or city name');
  } else {
    $city = $_POST['town-city'];
  }

  if (!isset($_POST['post-code']) && mb_strlen((string) $_POST['post-code']) < 1) {
    throw new Exception('You must provide a post code');
  } else {
    $postCode = (string) $PostcodeFormatter->format('GB', trim($_POST['post-code']));
  }

  $addr = [
    'streetAndNumber' => $streetAndNumber,
    'flatOrBuilding' => $flatOrBuilding,
    'city' => $city,
    'postCode' => $postCode,
  ];

  $json = json_encode($addr);

  $uuid = Ramsey\Uuid\Uuid::uuid4();
  $id = $uuid->toString();

  $add = $db->prepare("INSERT INTO covidLocations (`ID`, `Name`, `Address`, `Tenant`) VALUES (?, ?, ?, ?)");
  $add->execute([
    $id,
    mb_strimwidth($_POST['location-name'], 0, 256),
    $json,
    $tenant->getId()
  ]);

  $_SESSION['TENANT-' . app()->tenant->getId()]['NewLocationSuccess'] = true;
  header("location: " . autoUrl("contact-tracing/locations/" . $id));

} catch (PDOException $e) {
  throw new Exception('A database error occurred');
} catch (Exception $e) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['NewLocationError'] = $e->getMessage();
  header("location: " . autoUrl("contact-tracing/locations/new"));
}