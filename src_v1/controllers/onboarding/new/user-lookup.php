<?php

$db = app()->db;
$tenant = app()->tenant;

if (!isset($_POST['email'])) halt(404);

$getUser = $db->prepare("SELECT UserID id, Forename firstName, Surname lastName, EmailAddress email, Mobile phone FROM users WHERE EmailAddress = ? AND Tenant = ? AND Active");
$getUser->execute([
  $_POST['email'],
  $tenant->getId(),
]);
$user = $getUser->fetch(PDO::FETCH_OBJ);

$redirect = null;
if ($user) {
  $redirect = autoUrl('onboarding/new?user=' . urlencode($user->id));
}

header('content-type: application/json');
echo json_encode([
  'userExists' => $user != null,
  'redirect' => $redirect,
]);