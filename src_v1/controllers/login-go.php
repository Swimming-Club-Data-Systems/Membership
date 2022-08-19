<?php

use GeoIp2\Database\Reader;

$headerSent = false;

$db = app()->db;
$tenant = app()->tenant;

$incrementFailedLoginCount = $db->prepare("UPDATE users SET WrongPassCount = WrongPassCount + 1 WHERE UserID = ?");
$resetFailedLoginCount = $db->prepare("UPDATE users SET WrongPassCount = 0 WHERE UserID = ?");

$errorMessage = "";
$errorState = false;
$target = "";

$security_status = false;

if ($_POST['SessionSecurity'] == session_id()) {
  $security_status = true;
} else {
  $security_status = false;
}

if (SCDS\CSRF::verify()) {
  $security_status = true;
} else {
  $security_status = false;
}

if ((!empty($_POST['email-address']) && !empty($_POST['password'])) && ($security_status)) {
  // Let the user login
  $username = trim(mb_strtolower($_POST['email-address']));
  $target = ltrim(trim($_POST['target']), '/');

  $getUser = $db->prepare("SELECT Forename, Surname, UserID, EmailAddress, `Password`, WrongPassCount FROM users WHERE EmailAddress = ? AND Tenant = ? AND Active");
  $getUser->execute([
    $_POST['email-address'],
    $tenant->getId()
  ]);

  $row = $getUser->fetch(PDO::FETCH_ASSOC);

  if ($row != null) {
    $hash = $row['Password'];
    $email = $row['EmailAddress'];
    $forename = $row['Forename'];
    $surname = $row['Surname'];
    $userID = $row['UserID'];

    $verified = password_verify($_POST['password'], $hash);

    // if ($verified && CheckPwned::pwned($_POST['password'])) {
    //   // The password is pwned. It must be reset.
    // }

    if ($verified) {
      // Do 2FA
      if (bool(getUserOption($userID, "hasGoogleAuth2FA"))) {
        $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_GOOGLE'] = true;
      } else {
        $code = random_int(100000, 999999);

        // if ($do_random_2FA && !($isNotJustParent || bool(getUserOption($userID, "Is2FA")))) {
        //   setUserOption($userID, "IsSpotCheck2FA", true);
        // }

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
          halt(500);
        }
      }
      $_SESSION['TENANT-' . app()->tenant->getId()]['2FAUserID'] = $userID;
      if (isset($_POST['RememberMe']) && bool($_POST['RememberMe'])) {
        $_SESSION['TENANT-' . app()->tenant->getId()]['2FAUserRememberMe'] = true;
      }
      $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR'] = true;
      // reportError([
      //   '1',
      //   autoUrl("2fa?target=" . urlencode($_POST['target'])),
      // ]);
      if (!$headerSent) {
        header("Location: " . autoUrl("2fa?target=" . urlencode($_POST['target'])));
        $headerSent = true;
      }
    } else {
      // Incorrect PW
      // Don't notify user of error
      // Increment failed login count
      $incrementFailedLoginCount->execute([$userID]);

      // Set error state
      $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'] = true;
      $_SESSION['TENANT-' . app()->tenant->getId()]['EnteredUsername'] = $username;
    }
  } else {
    $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'] = true;
    $_SESSION['TENANT-' . app()->tenant->getId()]['EnteredUsername'] = $username;
  }
} else {
  if (!$security_status) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'] = true;
    $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStateLSVMessage'] = "We were unable to verify the integrity of your login attempt. The site you entered your email address and password on may have been attempting to capture your login details. Try reseting your password urgently.";
    $_SESSION['TENANT-' . app()->tenant->getId()]['InfoSec'] = [$_POST['LoginSecurityValue'], $_SESSION['TENANT-' . app()->tenant->getId()]['LoginSec']];
  }
}
$_SESSION['TENANT-' . app()->tenant->getId()]['InfoSec'] = [$_POST['LoginSecurityValue'], $_SESSION['TENANT-' . app()->tenant->getId()]['LoginSec']];
unset($_SESSION['TENANT-' . app()->tenant->getId()]['LoginSec']);
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']) && $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'] && $_POST['target'] == "" || isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorAccountLocked']) && $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorAccountLocked'] && $_POST['target'] == "") {
  // reportError([
  //   '2',
  //   autoUrl("login")
  // ]);
  if (!$headerSent) {
    header("Location: " . autoUrl("login"));
    $headerSent = true;
  }
} else {
  // reportError([
  //   '3',
  //   autoUrl(ltrim($_POST['target'], '/'), false),
  //   ltrim($_POST['target'], '/')
  // ]);
  if (!$headerSent) {
    if (isset($_POST['target']) && $_POST['target']) {
      header("Location: " . autoUrl(ltrim($_POST['target'], '/'), false));
    } else {
      header("Location: " . autoUrl(''));
    }
    $headerSent = true;
  }
}

if (!$headerSent) {
  header("Location: " . autoUrl(''));
  $headerSent = true;
}
