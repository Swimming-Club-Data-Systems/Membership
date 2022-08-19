<?php

$db = app()->db;
$tenant = app()->tenant;

$checkoutSession = \SCDS\Checkout\Session::retrieve($id);

$exCheckout = false;
if (isset($_SESSION['OnboardingSessionId'])) {
  // Validate
  $session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);
  $exCheckout = $session->user == $checkoutSession->user;
}

if ($checkoutSession->user && ((isset(app()->user) && $checkoutSession->user != app()->user->getId()) || (isset($_SESSION['OnboardingSessionId']) && !$exCheckout))) {
  reportError([$checkoutSession,]);
  halt(404);
}

// If not complete
if ($checkoutSession->state == 'succeeded' || $checkoutSession->getPaymentIntent()->status == 'succeeded') {
  // Else
  include 'success.php';
} else if ($checkoutSession->state == 'open') {
  include 'checkout.php';
}
