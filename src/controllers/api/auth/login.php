<?php

use GeoIp2\Database\Reader;

$db = app()->db;
$tenant = app()->tenant;

$incrementFailedLoginCount = $db->prepare("UPDATE users SET WrongPassCount = WrongPassCount + 1 WHERE UserID = ?");
$resetFailedLoginCount = $db->prepare("UPDATE users SET WrongPassCount = 0 WHERE UserID = ?");

$output = [];

$noMatch = new Exception('Email address or password was incorrect');

try {

  $json = json_decode(file_get_contents('php://input'));

  $email = trim(mb_strtolower($json->email_address));

  $getUser = $db->prepare("SELECT Forename, Surname, UserID, EmailAddress, `Password`, WrongPassCount FROM users WHERE EmailAddress = ? AND Tenant = ? AND Active");
  $getUser->execute([
    $email,
    $tenant->getId()
  ]);

  $row = $getUser->fetch(PDO::FETCH_ASSOC);

  if ($row) {
    $hash = $row['Password'];
    $email = $row['EmailAddress'];
    $forename = $row['Forename'];
    $surname = $row['Surname'];
    $userID = $row['UserID'];

    $verified = password_verify($json->password, $hash);

    if ($verified) {

      $hasTotp = bool(getUserOption($userID, "hasGoogleAuth2FA"));

      // Do 2FA
      if ($hasTotp) {
        $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_GOOGLE'] = true;

        $output = [
          'success' => true,
          'message' => 'Please enter your time-based one-time password'
        ];

      } else {
        $code = random_int(100000, 999999);

        $browserDetails = new \WhichBrowser\Parser(getallheaders());

        $message = '
          <p>Hello. Confirm your login by entering the following code in your web browser.</p>
          <p><strong>' . htmlspecialchars($code) . '</strong></p>
          <p>The login was from IP address ' . htmlspecialchars(getUserIp()) . ' using ' . htmlspecialchars($browserDetails->toString()) . '. If you did not just try to log in, you should reset your password immediately.</p>
          <p>Kind Regards, <br>The ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Team</p>';

        $date = new DateTime('now', new DateTimeZone('Europe/London'));

        if (notifySend(null, "Verification Code - Requested at " . $date->format("H:i:s \o\\n d/m/Y"), $message, $forename . " " . $surname, $email)) {
          $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_CODE'] = $code;
        } else {
          throw new Exception("Unable to send verification code email");
        }
      }
      $_SESSION['TENANT-' . app()->tenant->getId()]['2FAUserID'] = $userID;
      if (isset($_POST['RememberMe']) && bool($_POST['RememberMe'])) {
        $_SESSION['TENANT-' . app()->tenant->getId()]['2FAUserRememberMe'] = true;
      }
      $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR'] = true;

      $output = [
        'success' => true,
        'email' => $email,
        'message' => 'We have sent a two-factor authentication code to your email address',
        'has_totp' => $hasTotp,
        'two_factor_method' => $hasTotp ? 'totp' : 'email',
      ];

    } else {
      throw $noMatch;
    }
  } else {
    throw $noMatch;
  }

} catch (Exception $e) {
  $output = [
    'success' => false,
    'message' => $e->getMessage()
  ];
}

echo json_encode($output);