<?php

$db = app()->db;
$tenant = app()->tenant;
$currentUser = app()->user;

try {

  if (!isset($_GET['email'])) {
    throw new Exception("No email address was supplied");
  }

  if (!$currentUser->hasPermissions(["Admin"])) {
    throw new Exception("Unauthorised");
  }

  $getUser = $db->prepare("SELECT UserID id, Forename firstName, Surname lastName, EmailAddress email, Mobile phone FROM users WHERE EmailAddress = ? AND Tenant = ? AND Active");
  $getUser->execute([
    $_GET['email'],
    $tenant->getId(),
  ]);
  $user = $getUser->fetch(PDO::FETCH_OBJ);

  if (!$user) {
    $output = [
      'success' => true,
      'user' => null,
    ];
  } else {
    $output = [
      'success' => true,
      'user' => [
        'first_name' => $user->firstName,
        'last_name' => $user->lastName,
        'mobile' => $user->phone,
      ],
    ];
  }
} catch (Exception $e) {
  $output = [
    'success' => false,
    'message' => $e->getMessage()
  ];
}

echo json_encode($output);
