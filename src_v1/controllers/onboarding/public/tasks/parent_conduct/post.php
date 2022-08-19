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

$good = false;

$parentCode = app()->tenant->getKey('ParentCodeOfConduct');

if ($parentCode) {
  $good = isset($_POST['agree']);
}

if ($good) {
  // If all good,

  // Set complete
  $session->completeTask('parent_conduct');

  header('location: ' . autoUrl('onboarding/go'));
} else {
  $_SESSION['FormError'] = true;
  header('location: ' . autoUrl('onboarding/go/start-task'));
}
