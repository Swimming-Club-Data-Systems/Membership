<?php

$db = app()->db;

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

// Validate and update user info

$contacts = new EmergencyContacts($db);
$contacts->byParent($user->getId());
$good = sizeof($contacts->getContacts()) > 0;

if ($good) {
  // If all good,

  // Set complete
  $session->completeTask('emergency_contacts');

  header('location: ' . autoUrl('onboarding/go'));
} else {
  header('location: ' . autoUrl('onboarding/go/start-task'));
}
