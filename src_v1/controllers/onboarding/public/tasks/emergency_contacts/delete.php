<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$db = app()->db;

$good = true;

$contact = new EmergencyContact();
$contact->connect($db);
$contact->getByContactID($_POST['id']);

if ($contact->getUserID() == $user->getId()) {
  $contact->delete();
} else {
  $good = false;
}

header("content-type: application/json");

echo json_encode([
  'success' => $good,
]);
