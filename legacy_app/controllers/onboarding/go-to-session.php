<?php

if (!isset($_GET['session'])) halt(404);

$session = \SCDS\Onboarding\Session::retrieve($_GET['session']);

if ($session->status == 'not_ready') {
  header("location: " . autoUrl("onboarding/go/error"));
} else if (Auth::User()->getLegacyUser() !== null && Auth::id() != $session->user) {
  header("location: " . autoUrl("onboarding"));
} else {
  // Good to go

  // Login?

  // Redirect
  $_SESSION['OnboardingSessionId'] = $session->id;
  header("location: " . autoUrl("onboarding/go"));
}
