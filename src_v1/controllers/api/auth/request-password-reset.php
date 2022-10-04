<?php

$db = app()->db;
$tenant = app()->tenant;

$output = [];

try {

  $json = json_decode(file_get_contents('php://input'));

  $email = trim(mb_strtolower($json->email_address));

  // Test for valid email
  $findUser = $db->prepare("SELECT UserID, Forename, Surname, EmailAddress FROM users WHERE EmailAddress = ? AND Tenant = ? AND Active");
  $findUser->execute([
    $email,
    $tenant->getId()
  ]);

  $row = $findUser->fetch(PDO::FETCH_ASSOC);

  if ($row) {
    $userID = $row['UserID'];

    $resetLink = $userID . "-reset-" . hash('sha256', random_int(PHP_INT_MIN, PHP_INT_MAX) . time());

    $insertToDb = $db->prepare("INSERT INTO passwordTokens (`UserID`, `Token`, `Type`) VALUES (?, ?, ?)");
    $insertToDb->execute([
      $row['UserID'],
      $resetLink,
      'Password_Reset'
    ]);

    $link = autoUrl("login/reset-password?auth-code=" . urlencode($resetLink));

    $subject = "Password Reset for " . $row['Forename'] . " " . $row['Surname'];
    $sContent = '
    <p>Hi ' . htmlspecialchars($row['Forename']) . '</p>
    <p>Here\'s your <a href="' . $link . '">password reset link - ' . $link . '</a>.</p>
    <p>Follow this link to reset your password quickly and easily.</p>
    <p>If you did not request a password reset, please delete and ignore this email.</p>
    ';

    notifySend(null, $subject, $sContent, $row['Forename'] . " " . $row['Surname'], $row['EmailAddress'], ["Email" =>
    "noreply@" . getenv('EMAIL_DOMAIN'), "Name" => app()->tenant->getKey('CLUB_NAME') . " Account Help"]);
  } else {
    throw new Exception("We couldn't find a matching account");
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