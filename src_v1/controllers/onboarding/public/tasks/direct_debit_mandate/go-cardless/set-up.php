<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(503);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$db = app()->db;

// if (!app()->tenant->getBooleanKey('ALLOW_STRIPE_DIRECT_DEBIT_SET_UP') || !app()->tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT') || !getenv('STRIPE')) halt(403);

$client = null;
try {
  $client = SCDS\GoCardless\Client::get();
} catch (Exception $e) {
  halt(404);
}

$successUrl = autoUrl('onboarding/go/direct-debit/go-cardless/success');
$cancelUrl = autoUrl('onboarding/go/start-task');

$scheduleExists = false;
try {
  $getPaySchdeule = $db->prepare("SELECT * FROM `paymentSchedule` WHERE `UserID` = ?");
  $getPaySchdeule->execute([$user->getId()]);
  $scheduleExists = $getPaySchdeule->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  halt(500);
}

if ($scheduleExists == null) {
  // Setup schedule
  $date = 1;

  $insert = $db->prepare("INSERT INTO `paymentSchedule` (`UserID`, `Day`) VALUES (?, ?)");
  $insert->execute([$user->getId(), $date]);
}

$getDetails = $db->prepare("SELECT Forename, Surname, EmailAddress FROM users WHERE UserID = ?");
$getDetails->execute([$user->getId()]);
$row = $getDetails->fetch(PDO::FETCH_ASSOC);

$_SESSION['TENANT-' . app()->tenant->getId()]['Token'] = hash('sha256', $user->getId() . "-" . rand(1000, 9999));

$addr = null;
$json = $user->getUserOption('MAIN_ADDRESS');
if ($json != null) {
  $addr = json_decode($json);
}

$prefilledCustomer = [
  "given_name" => $row['Forename'],
  "family_name" => $row['Surname'],
  "email" => $row['EmailAddress']
];
if ($addr != null) {
  if (isset($addr->streetAndNumber)) {
    $prefilledCustomer += ['address_line1' => $addr->streetAndNumber];
  }
  if (isset($addr->flatOrBuilding)) {
    $prefilledCustomer += ['address_line2' => $addr->flatOrBuilding];
  }
  if (isset($addr->city)) {
    $prefilledCustomer += ['city' => $addr->city];
  }
  if (isset($addr->postCode)) {
    $prefilledCustomer += ['postal_code' => $addr->postCode];
  }
}

try {
  $redirectFlow = $client->redirectFlows()->create([
    "params" => [
      // This will be shown on the payment pages
      "description" => "Club fee payments",
      // Not the access token
      "session_token" => $_SESSION['TENANT-' . app()->tenant->getId()]['Token'],
      "success_redirect_url" => $successUrl,
      // Optionally, prefill customer details on the payment page
      "prefilled_customer" => $prefilledCustomer
    ]
  ]);

  // Hold on to this ID - you'll need it when you
  // "confirm" the redirect flow later
  $_SESSION['TENANT-' . app()->tenant->getId()]['GC_REDIRECTFLOW_ID'] = $redirectFlow->id;
  http_response_code(303);
  header("Location: " . $redirectFlow->redirect_url);
} catch (Exception $e) {
  halt(902);
}
