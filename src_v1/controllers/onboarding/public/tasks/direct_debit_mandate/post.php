<?php

$db = app()->db;

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$pagetitle = 'Direct Debit Instruction - Onboarding';

$good = false;

if (isset($_SESSION['SetupMandateSuccess'])) {
  $good = true;
}

$ddi = null;
if (app()->tenant->getBooleanKey('ALLOW_STRIPE_DIRECT_DEBIT_SET_UP') && app()->tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT')) {
  // Get DD details
  // Get mandates
  $getMandates = $db->prepare("SELECT ID, Mandate, Last4, SortCode, `Address`, Reference, `URL`, `Status` FROM stripeMandates WHERE Customer = ? AND (`Status` = 'accepted' OR `Status` = 'pending') ORDER BY CreationTime DESC");
  $getMandates->execute([
    $user->getStripeCustomer()->id,
  ]);
  $mandate = $getMandates->fetch(PDO::FETCH_ASSOC);

  if ($mandate) {
    $good = true;
  }
} else if ($tenant->getGoCardlessAccessToken()) {
  $good = userHasMandates($user->getId());
}

if (isset($_SESSION['SetupMandateSuccess'])) {
  unset($_SESSION['SetupMandateSuccess']);
}

if ($good || $tenant->getBooleanKey('ALLOW_DIRECT_DEBIT_OPT_OUT')) {
  // If all good,

  // Set complete
  $session->completeTask('direct_debit_mandate');

  header('location: ' . autoUrl('onboarding/go'));
} else {
  header('location: ' . autoUrl('onboarding/go/start-task'));
}
