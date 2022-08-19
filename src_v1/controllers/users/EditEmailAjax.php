<?php

use Respect\Validation\Validator as v;
$email = trim(mb_convert_case($_POST['email'], MB_CASE_LOWER));

$valid = true;
$message = 'Email address is allowed.';

try {
  $db = app()->db;
  $tenant = app()->tenant;

  $get = $db->prepare("SELECT COUNT(*) FROM users WHERE EmailAddress = ? AND UserID != ?");
  $get->execute([
    $email,
    $id
  ]);

  if ($get->fetchColumn() > 0) {
    $valid = false;
  }

  if (!$valid) {
    $getUser = $db->prepare("SELECT Forename, Surname FROM users WHERE EmailAddress = ?");
    $getUser->execute([
      $email
    ]);
    $u = $getUser->fetch(PDO::FETCH_ASSOC);
    $message = $email . ' is already in use by ' . $u['Forename'] . ' ' . $u['Surname'] . '.';
  }

  if (!v::email()->validate($email)) {
    $valid = false;
    $message = $email . ' is not a valid email address.';
  }

} catch (Exception $e) {
  $valid = false;
  $message = 'An unknown error occurred while verifying the email address.';
}

header('Content-Type: application/json');
echo json_encode(['valid' => $valid, 'message' => $message]);