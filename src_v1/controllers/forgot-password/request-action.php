<?php

if (!SCDS\CSRF::verify()) {
  halt(403);
}

$pagetitle = "Password Reset";
include BASE_PATH . "views/header.php";

$db = app()->db;
$tenant = app()->tenant;

$userDetails = mb_strtolower(trim($_POST['email-address']));
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
if (!$result->success) { ?>
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-sm-6 col-md-5 col-lg4">
        <div class="alert alert-danger">
          <strong>Captcha Verification Failed</strong>
          <p class="mb-0">You must prove that you are human.</p>
        </div>
      </div>
    </div>
  </div>
  <?php
} else {
  $captchaStatus = true;
  $found = false;
  $row = null;

  // Test for valid email
  $findUser = $db->prepare("SELECT UserID, Forename, Surname, EmailAddress FROM users WHERE EmailAddress = ? AND Tenant = ?");
  $findUser->execute([
    $userDetails,
    $tenant->getId()
  ]);

  if ($row = $findUser->fetch(PDO::FETCH_ASSOC)) {
    $found = true;
  }

  if ($found == true) {
    $userID = $row['UserID'];

    $resetLink = $userID . "-reset-" . hash('sha256', random_int(0, 999999999999) . time());

    $insertToDb = $db->prepare("INSERT INTO passwordTokens (`UserID`, `Token`, `Type`) VALUES (?, ?, ?)");
    $insertToDb->execute([
      $row['UserID'],
      $resetLink,
      'Password_Reset'
    ]);

    // PHP Email
    $subject = "Password Reset for " . $row['Forename'] . " " . $row['Surname'];
    $sContent = '
    <h1>Hello ' . htmlspecialchars($row['Forename']) . '</h1>
    <p>Here\'s your <a href="' . autoUrl("resetpassword/auth/" . $resetLink) . '">password reset link - ' . autoUrl("resetpassword/auth/" . $resetLink) . '</a>.</p>
    <p>Follow this link to reset your password quickly and easily.</p>
    <p>If you did not request a password reset, please delete and ignore this email.</p>
    ';

    if (notifySend(null, $subject, $sContent, $row['Forename'] . " " .
      $row['Surname'], $row['EmailAddress'], ["Email" =>
    "noreply@" . getenv('EMAIL_DOMAIN'), "Name" => app()->tenant->getKey('CLUB_NAME') . " Account Help"])) {
  ?>
      <div class="container-fluid">
        <div class="row justify-content-center">
          <div class="col-sm-6 col-md-5 col-lg4">
            <div class="alert alert-success">
              <strong>We found your account and have sent you an email to reset your password</strong>
              <p class="mb-2">Check your email account and follow the link to reset your password.</a>.</p>
              <p class="mb-0">If you request another password reset, only the most recent link will work.</p>
            </div>
          </div>
        </div>
      </div>
    <?php
    } else {
    ?>
      <div class="container-fluid">
        <div class="row justify-content-center">
          <div class="col-sm-6 col-md-5 col-lg4">
            <div class="alert alert-warning">
              <p class="mb-0">
                <strong>We were unable to send password reset details to your email address</strong>
              </p>
              <p>
                If you do not have an account, please ask your club to create an account for you or <a href="<?= autoUrl("resetpassword") ?>" class="alert-link">try again</a>
              </p>
              <p class="mb-0">
                Contact our support team if the issue persists.
              </p>
            </div>
          </div>
        </div>
      </div>
    <?php
    }
  } else {
    // error
    ?>
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-sm-6 col-md-5 col-lg4">
          <div class="alert alert-warning">
            <strong>We did not find an account using those details</strong>
            <p>If you do not have an account, <a href="<?= autoUrl("register") ?>" class="alert-link">register for an account</a></p>
            <p>Or, <a href="<?= autoUrl("resetpassword") ?>" class="alert-link">try again</a></p>
          </div>
        </div>
      </div>
    </div>
<?php
  }
}


?>
<?php include BASE_PATH . "views/footer.php" ?>