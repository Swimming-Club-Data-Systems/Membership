<?php

use Respect\Validation\Validator as v;

$db = app()->db;
$tenant = app()->tenant;

$output = [];

try {

  $json = json_decode(file_get_contents('php://input'));

  $token = trim(mb_strtolower($json->token));

  $getUser = $db->prepare("SELECT users.UserID, users.Forename, users.Surname, passwordTokens.Date FROM passwordTokens INNER JOIN users ON users.UserID = passwordTokens.UserID WHERE `Type` = ? AND `Token` = ? AND users.Tenant = ? ORDER BY TokenID DESC LIMIT 1");
  $getUser->execute([
    "Password_Reset",
    $token,
    $tenant->getId()
  ]);

  $user = $getUser->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    throw new Exception("No matching user reset request found");
  }

  $resetDate = new DateTime('now', new DateTimeZone("Europe/London"));
  $date = new DateTime('now', new DateTimeZone("Europe/London"));
  $date = $date->sub(new DateInterval("P2D"));

  if ($date > $resetDate) {
    throw new Exception("The password reset token has expired");
  }


  $password = trim($json->password);

  if (v::stringType()->length(8, null)->validate($password) && !(\CheckPwned::pwned($password))) {
    // Set the password
    $newHash = password_hash($password, PASSWORD_ARGON2ID);

    // Update the password in db
    $updatePass = $db->prepare("UPDATE `users` SET `Password` = ? WHERE `UserID` = ?");
    $updatePass->execute([$newHash, $user['UserID']]);

    // Remove token from db
    $deleteToken = $db->prepare("DELETE FROM `passwordTokens` WHERE `UserID` = ?");
    $deleteToken->execute([$user['UserID']]);
  } else {
    // Invalid
    throw new Exception("Invalid password");
  }

  $output = [
    "success" => true,
  ];
} catch (Exception $e) {

  $output = [
    "success" => false,
    "message" => $e->getMessage(),
  ];
}

echo json_encode($output);
