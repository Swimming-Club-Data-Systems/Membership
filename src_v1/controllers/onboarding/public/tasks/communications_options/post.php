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

$good = true;

if ($good) {
  // If all good,
  $update = $db->prepare("UPDATE `users` SET `EmailComms` = ?, `MobileComms` = ? WHERE `UserID` = ?");
  $update->execute([
    (int) isset($_POST['emailContactOK']),
    (int) isset($_POST['smsContactOK']),
    $user->getId(),
  ]);

  updateSubscription(isset($_POST['PaymentComms']), 'Payments', $user->getId());
  if ($user->hasPermission('Admin')) {
    updateSubscription(isset($_POST['NewMemberComms']), 'NewMember', $user->getId());
  }

  $getCategories = $db->prepare("SELECT `ID` `id`, `Name` `name`, `Description` `description` FROM `notifyCategories` WHERE `Tenant` = ? AND `Active` ORDER BY `Name` ASC;");
  $getCategories->execute([
    $tenant->getId()
  ]);

  while ($category = $getCategories->fetch(PDO::FETCH_OBJ)) {
    updateSubscription(isset($_POST['email-category-' . $category->id]), $category->id, $user->getId());
  }

  // Set complete
  $session->completeTask('communications_options');

  header('location: ' . autoUrl('onboarding/go'));
} else {
  $_SESSION['FormError'] = true;
  header('location: ' . autoUrl('onboarding/go/start-task'));
}
