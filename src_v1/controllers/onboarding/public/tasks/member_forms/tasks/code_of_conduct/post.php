<?php

$db = app()->db;

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Member::stagesOrder();

// Get member
$onboardingMember = \SCDS\Onboarding\Member::retrieveById($id);

$member = $onboardingMember->getMember();

$good = true;

if (!\SCDS\CSRF::verify()) halt(403);

if (!(isset($_POST['agree']) && bool($_POST['agree']))) {
  $good = false;
}

if ($member->getAge() < 18 && !(isset($_POST['agree']) && bool($_POST['agree']))) {
  $good = false;
}

if ($good) {
  $onboardingMember->completeTask('code_of_conduct');

  http_response_code(302);
  if ($onboardingMember->session->checkMemberTasksComplete()) {
    header("Location: " . autoUrl('onboarding/go'));
  } else {
    header("Location: " . autoUrl('onboarding/go/start-task'));
  }
} else {

  $_SESSION['FormError'] = true;

  http_response_code(302);
  header("Location: " . autoUrl('onboarding/go/member-forms/' . $onboardingMember->id . '/start-task'));
}
