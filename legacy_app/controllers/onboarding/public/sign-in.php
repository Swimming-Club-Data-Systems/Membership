<?php

$db = DB::connection()->getPdo();

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = tenant()->getLegacyTenant();

if ($session->isCurrentTask('done') && !Auth::User()->getLegacyUser() !== null) {
  try {
    $login = new \CLSASC\Membership\Login($db);
    $login->setUser($session->user);
    $login->stayLoggedIn();
    $login->preventWarningEmail();
    $currentUser = Auth::User()->getLegacyUser();
    $currentUser = $login->login();

    $_SESSION['OnboardingSessionId'] = null;
    unset($_SESSION['OnboardingSessionId']);

    header("location: " . autoUrl(''));
  } catch (Exception $e) {
    halt(403);
  }
} else {
  halt(404);
}
