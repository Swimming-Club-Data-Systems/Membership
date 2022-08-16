<?php

$db = DB::connection()->getPdo();

$url_path = "payments";
if (isset($renewal_trap) && $renewal_trap) {
	$url_path = "renewal/payments";
}

$user = $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'];

$scheduleExists = false;
try {
  $getPaySchdeule = $db->prepare("SELECT * FROM `paymentSchedule` WHERE `UserID` = ?");
  $getPaySchdeule->execute([$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID']]);
  $scheduleExists = $getPaySchdeule->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  halt(500);
}

if ($scheduleExists == null) {
  header("Location: " . autoUrl($url_path . "/setup/0"));
} else {
  $getDetails = $db->prepare("SELECT Forename, Surname, EmailAddress FROM users WHERE UserID = ?");
  $getDetails->execute([$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID']]);
  $row = $getDetails->fetch(PDO::FETCH_ASSOC);

  $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['Token'] = hash('sha256', $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'] . "-" . rand(1000,9999));

  $addr = null;
  $currentUser = Auth::User()->getLegacyUser();
  $json = $currentUser->getUserOption('MAIN_ADDRESS');
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
        "session_token" => $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['Token'],
        "success_redirect_url" => autoUrl($url_path . "/setup/3"),
        // Optionally, prefill customer details on the payment page
        "prefilled_customer" => $prefilledCustomer
      ]
    ]);

    // Hold on to this ID - you'll need it when you
    // "confirm" the redirect flow later
    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['GC_REDIRECTFLOW_ID'] = $redirectFlow->id;

    header("Location: " . $redirectFlow->redirect_url);
  } catch (Exception $e) {
    halt(902);
  }
}
