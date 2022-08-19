<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

if (!$session->batch) halt(503);

if (!isset($_GET['checkout_session'])) halt(404);

$checkoutSession = \SCDS\Checkout\Session::retrieve($_GET['checkout_session']);

$intent = $checkoutSession->getPaymentIntent();

if ($intent->status == 'succeeded') {
  // It worked
  $session->completeTask('fees');

  $_SESSION['PaymentSuccess'] = true;

  header('location: ' . autoUrl('onboarding/go'));
} else {
  // Go back
  header('location: ' . autoUrl('onboarding/go/start-task'));
}