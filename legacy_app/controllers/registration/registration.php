<?php

if (!SCDS\CSRF::verify()) {
  halt(403);
}

use Respect\Validation\Validator as v;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;

$db = app()->db;

$captcha = trim($_POST['g-recaptcha-response']);
$captchaStatus = null;

#
# Verify captcha
$post_data = http_build_query([
  'secret' => getenv('GOOGLE_RECAPTCHA_SECRET'),
  'response' => $_POST['g-recaptcha-response'],
  'remoteip' => getUserIp()
]);
$opts = array('http' => [
  'method'  => 'POST',
  'header'  => 'Content-type: application/x-www-form-urlencoded',
  'content' => $post_data
]);
$context  = stream_context_create($opts);
$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
$result = json_decode($response);

if ($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationMode'] == "Family-Manual") {
  $sql = "SELECT * FROM `familyIdentifiers` WHERE `ID` = ? AND `ACS` = ?";

  $fid = trim(str_replace(["FAM", "fam"], "", $_POST['fam-reg-num']));
  $acs = trim($_POST['fam-sec-key']);

  try {
  	$query = $db->prepare($sql);
  	$query->execute([$fid, $acs]);
  } catch (PDOException $e) {
  	halt(500);
  }

  $row = $query->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
  	$_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationFamNum'] = htmlentities($fid);
    $_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationFamKey'] = htmlentities($acs);
  }

  $_SESSION['TENANT-' . app()->tenant->getId()]['FamilyIdentifier'] = $fid;
}

// Registration Form Handler


$status = true;
$statusMessage = "";

$forename = trim(ucwords($_POST['forename']));
$surname = trim(ucwords($_POST['surname']));
$username = $forename . $surname . "-" . md5(generateRandomString(20) . time());
$password1 = trim($_POST['password1']);
$password2 = trim($_POST['password2']);
$email = mb_strtolower(trim($_POST['email']));
$mobile = null;
try {
  $number = PhoneNumber::parse($_POST['mobile'], 'GB');
  $mobile = $number->format(PhoneNumberFormat::E164);
}
catch (PhoneNumberParseException $e) {
  // 'The string supplied is too short to be a phone number.'
  $status = false;
  $statusMessage .= "
  <li>That phone number is not valid</li>
  ";
}
$emailAuth = 0;
if ($_POST['emailAuthorise'] != 1) {
  $emailAuth = 0;
} else {
  $emailAuth = 1;
}
$smsAuth = 0;
if ($_POST['smsAuthorise'] != 1) {
  $smsAuth = 0;
} else {
  $smsAuth = 1;
}

if (!v::stringType()->length(7, null)->validate($password1)) {
  $status = false;
  $statusMessage .= "
  <li>Password does not meet the password length requirements. Passwords must be
  8 characters or longer</li>
  ";
}

if (!v::email()->validate($email)) {
  $status = false;
  $statusMessage .= "
  <li>That email address is not valid</li>
  ";
}

if ($password1 != $password2) {
  $status = false;
  $statusMessage .= "
  <li>Passwords do not match</li>
  ";
}

if (!$result->success) {
  $status = false;
  $statusMessage .= "
  <li>We couldn't verify you're a human</li>
  ";
}

$getEmailCount = $db->prepare("SELECT COUNT(*) FROM users WHERE EmailAddress = ?");
$getEmailCount->execute([$email]);
if ($getEmailCount->fetchColumn() > 0) {
  $status = false;
  $statusMessage .= "
  <li>That email address is already in use</li>
  ";
}

$hashedPassword = password_hash($password1, PASSWORD_BCRYPT);

$account = [
  "Forename"          => $forename,
  "Surname"           => $surname,
  "Username"          => $username,
  "Password"          => $hashedPassword,
  "EmailAddress"      => $email,
  "EmailComms"        => $emailAuth,
  "Mobile"            => $mobile,
  "MobileComms"       => $smsAuth
];

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['FamilyIdentifier'])) {
  $account["FamilyIdentifier"] = $_SESSION['TENANT-' . app()->tenant->getId()]['FamilyIdentifier'];
  $account["RequiresRegistraion"] = true;
}

$accountJSON = json_encode($account);

if ($status) {
  // Registration may be allowed
  // Success
  $authCode = hash('sha256', random_int(0, 999999) . time());
  $addToDb = $db->prepare("INSERT INTO `newUsers` (`AuthCode`, `UserJSON`, `Type`) VALUES (?,
  ?, 'Registration')");
  $addToDb->execute([$authCode, $accountJSON]);
  $id = $db->lastInsertId();
  $verifyLink = "register/auth/" . $id . "/" . "new-user/" . $authCode;

  // PHP Email
  $subject = "Thanks for Joining " . $forename;
  $to = $email;
  $sContent = '
  <p class="small">Hello ' . htmlspecialchars($forename) . '</p>
  <p>Thanks for signing up for your ' . app()->tenant->getKey('CLUB_NAME') . ' Account.</p>
  <p>We need you to verify your email address by following this link - <a
  href="' . autoUrl($verifyLink) . '" target="_blank">' .
  autoUrl($verifyLink) . '</a></p>
  <p>You will use your email address, ' . htmlspecialchars($email) . ' to sign in.</p>
  <p>You can change your personal details and password in My Account</p>
  <p>For help please contact your club by email.</p>
  <script type="application/ld+json">
  {
    "@context": "http://schema.org",
    "@type": "EmailMessage",
    "potentialAction": {
      "@type": "ViewAction",
      "url": "' . autoUrl("") . '",
      "target": "' . autoUrl("") . '",
      "name": "Login"
    },
    "description": "Login to your accounts",
    "publisher": {
      "@type": "Organization",
      "name": "' . app()->tenant->getKey('CLUB_NAME') . '",
      "url": "https://www.chesterlestreetasc.co.uk",
      "url/googlePlus": "https://plus.google.com/110024389189196283575"
    }

  }
  </script>
  ';

  notifySend($to, $subject, $sContent, $forename . " " . $surname, $email, ["Email" => "registration@" . getenv('EMAIL_DOMAIN'), "Name" => app()->tenant->getKey('CLUB_NAME')]);

  $_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationGoVerify'] = '
  <div class="alert alert-success mb-0">
    <p class="mb-0">
      <strong>
        We now need you to verify your email address
      </strong>
    </p>

    <p class="mb-0">
      You\'ll find an email waiting for you in your inbox. Follow the link in
      there and we can finish your registration. You cannot complete account
      registration without verifying your email.
    </p>
  </div>
  ';

  header("Location: " . autoUrl("register"));
} else {
  $_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationUsername'] = $username;
  $_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationForename'] = $forename;
  $_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationSurname'] = $surname;
  $_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationEmail'] = $email;
  $_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationMobile'] = $mobile;
  if ($emailAuth == 1) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationEmailAuth'] = " checked ";
  }
  if ($smsAuth == 1) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationSmsAuth'] = " checked ";
  }

  $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'] = '
  <div class="alert alert-warning">
  <p><strong>Something wasn\'t right</strong></p>
  <ul class="mb-0">' . $statusMessage . '</ul></div>';

  header("Location: " . autoUrl("register"));
}
?>
