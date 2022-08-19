<?php

use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
$db = app()->db;

$incrementFailedLoginCount = $db->prepare("UPDATE users SET WrongPassCount = WrongPassCount + 1 WHERE UserID = ?");
$resetFailedLoginCount = $db->prepare("UPDATE users SET WrongPassCount = 0 WHERE UserID = ?");

$response = [
  'next' => 'error',
  'error' => [
    'message' => 'HI'
  ]
];

try {

  // Parse request
  $body = json_decode(file_get_contents('php://input'));

  if (!SCDS\CSRF::verifyCode($body->csrfToken)) {
    throw new Exception('csrf');
  }

  // Get the organisation
  $club = null;
  if (isset($body->organisation) && mb_strlen((string) $body->organisation) > 0) {
    $club = Tenant::fromCode((string) $body->organisation);
    if (!$club) {
      $club = Tenant::fromId((int) $body->organisation);
    }
  }

  if (!$club) {
    throw new Error('clubNotFound');
  }

  app()->tenant = $club;

  // Try finding user
  $email = mb_strtolower(trim($body->email));
  $password = $body->password;

  $getUser = $db->prepare("SELECT Forename, Surname, UserID, EmailAddress, `Password`, WrongPassCount FROM users WHERE EmailAddress = ? AND Tenant = ? AND Active");
  $getUser->execute([
    $email,
    $club->getId()
  ]);
  $user = $getUser->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    throw new Exception('incorrect');
  }

  if (!password_verify($password, $user['Password'])) {
    // Password is incorrect
    throw new Exception('incorrect');
  }

  // At this stage user is validated
  // Check if 2fa required
  $do_random_2FA = random_int(0, 99) < 5 || bool(getUserOption($user['UserID'], "IsSpotCheck2FA")) || $user['WrongPassCount'] > 2;

  $isJustParent = $db->prepare("SELECT COUNT(*) FROM `permissions` WHERE `User` = ? AND `Permission` != 'Parent';");
  $isJustParent->execute([
    $user['UserID']
  ]);
  $isNotJustParent = $isJustParent->fetchColumn() > 0;

  $uses2FA = bool(getUserOption($user['UserID'], "Is2FA"));

  if ($isNotJustParent || $uses2FA || $do_random_2FA) {
    $type = 'email';
    $reason = 'authority';

    if ($uses2FA) {
      $reason = 'user-enabled';
    }

    // 2FA Required
    if (bool(getUserOption($user['UserID'], "hasGoogleAuth2FA"))) {
      $_SESSION['TENANT-' . $club->getId()]['TWO_FACTOR_GOOGLE'] = true;
      $type = 'ga';
    } else {
      $code = random_int(100000, 999999);

      if ($do_random_2FA && !($isNotJustParent || bool(getUserOption($user['UserID'], "Is2FA")))) {
        setUserOption($user['UserID'], "IsSpotCheck2FA", true);
        $reason = 'spot-check';
      }

      $message = '
      <p>Hello. Confirm your login by entering the following code in your web browser.</p>
      <p><strong>' . $code . '</strong></p>
      <p>If you did not just try to log in, you may want to reset your password.</p>
      <p>Kind Regards, <br>The ' . $club->getKey('CLUB_NAME') . ' Team</p>';

      $date = new DateTime('now', new DateTimeZone('Europe/London'));

      if (notifySend(null, "Verification Code - Requested at " . $date->format("H:i:s \o\\n d/m/Y"), $message, $user['Forename'] . " " . $user['Surname'], $email)) {
        $_SESSION['TENANT-' . $club->getId()]['TWO_FACTOR_CODE'] = $code;
      } else {
        throw new Exception('notsent');
      }
    }

    $_SESSION['TENANT-' . $club->getId()]['2FAUserID'] = $user['UserID'];

    $response = [
      'next' => 'twoFactor',
      'twoFactor' => [
        'type' => $type,
        'reason' => $reason
      ]
    ];
  } else {

    $_SESSION['PassAuth-TENANT-' . $club->getId()]['User'] = $user['UserID'];

    $response = [
      'next' => 'pass',
      'pass' => [
        'redirect' => autoUrl("login/pass", false)
      ]
    ];
  }

} catch (Exception $e) {

  $error = $e->getMessage();

  if ($error == 'csrf') {
    $response = [
      'next' => 'csrf',
      'csrf' => [
        'message' => 'HI'
      ]
    ];
  } else if ($error == 'incorrect') {
    $response = [
      'next' => 'incorrect',
      'incorrect' => [
        'message' => 'HI'
      ]
    ];    
  } else if ($error == 'notSent') {
    $response = [
      'next' => 'notsent',
      'notSent' => [
        'message' => 'HI'
      ]
    ];
  } else {
    $response = [
      'next' => 'error',
      'error' => [
        'message' => 'HI'
      ]
    ];
  }

} finally {
  echo json_encode($response);
}