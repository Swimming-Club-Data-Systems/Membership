<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$db = app()->db;

$contact = new EmergencyContact();
$contact->connect($db);

$good = true;

$contact = new EmergencyContact();
$contact->connect($db);
$contact->getByContactID($_POST['contact-id']);

if ($contact->getUserID() == $user->getId()) {

  if ($_POST['name'] != null && $_POST['name'] != "") {
    $contact->setName($_POST['name']);
  }

  if ($_POST['relation'] != null && $_POST['relation'] != "") {
    $contact->setRelation($_POST['relation']);
  }

  try {
    if ($_POST['num'] != null && $_POST['num'] != "") {
      $contact->setContactNumber($_POST['num']);
    }
  } catch (Exception $e) {
    $good = false;
  }
} else {
  $good = false;
}

header("content-type: application/json");

echo json_encode([
  'success' => $good,
]);
