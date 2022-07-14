<?php

if (!isset($_GET['session']) || !isset($_GET['token'])) {
  header("location: " . autoUrl("onboarding/go/error"));
  return;
}

$session = \SCDS\Onboarding\Session::retrieve($_GET['session']);

if ($session->token != $_GET['token']) {
  header("location: " . autoUrl("onboarding/go/error"));
  return;
}

if ($session->status == 'not_ready') {
  header("location: " . autoUrl("onboarding/go/error"));
} else if (!$session->tokenOn) {
  header("location: " . autoUrl("onboarding/go/error"));
} else if (isset(app()->user) && app()->user->getId() != $session->user) {
  header("location: " . autoUrl("onboarding/go/wrong-account?session=" . urlencode($_GET['session']) . '&token=' . urlencode($_GET['token'])));
} else {
  // Good to go

  // Login only at the end if onboarding session

  // Redirect
  $_SESSION['OnboardingSessionId'] = $session->id;
  header("location: " . autoUrl("onboarding/go"));
}
