<?php

use Respect\Validation\Validator as v;

$db = app()->db;
$tenant = app()->tenant;

$getUser = $db->prepare("SELECT users.UserID FROM passwordTokens INNER JOIN users ON users.UserID = passwordTokens.UserID WHERE Token = ? AND users.Tenant = ? ORDER BY TokenID DESC LIMIT 1");
$getUser->execute([
  $token,
  $tenant->getId()
]);

if ($user = $getUser->fetchColumn()) {
  if ((isset($_POST['password-1']) && isset($_POST['password-2'])) && (trim($_POST['password-1']) == trim($_POST['password-2'])) && v::stringType()->length(8, null)->validate($_POST['password-1']) && !(\CheckPwned::pwned(trim($_POST['password-1'])))) {
    // Set the password
    $newHash = password_hash(trim($_POST['password-1']), PASSWORD_ARGON2ID);

    // Update the password in db
    $updatePass = $db->prepare("UPDATE users SET Password = ? WHERE UserID = ?");
    $updatePass->execute([$newHash, $user]);

    // Remove token from db
    $deleteToken = $db->prepare("DELETE FROM passwordTokens WHERE UserID = ?");
    $deleteToken->execute([$user]);

    // Display success
    include BASE_PATH . 'views/header.php';
?>
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-sm-6 col-md-5 col-lg4">
          <div class="alert alert-success">
            <strong>We've reset your Password</strong>
            <p class="mb-2">You can now, <a href="<?= autoUrl("login") ?>" class="alert-link">login with your new password</a>.</p>
            <p class="mb-0">Thank you for using this service.</p>
          </div>
        </div>
      </div>
    </div>
  <?php
    $footer = new \SCDS\Footer();
    $footer->render();
  } else {
    // Return as password error
    include BASE_PATH . 'views/header.php';
  ?>
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-sm-6 col-md-5 col-lg4">
          <div class="alert alert-danger">
            <strong>You failed to supply both passwords, the passwords did not match or the password was not 8 characters or more</strong>
            <p class="mb-2">Please, <a href="<?= autoUrl("resetpassword/auth/" . $token) ?>" class="alert-link">try again</a>.</p>
            <p class="mb-0">We're sorry for any inconvenience caused.</p>
          </div>
        </div>
      </div>
    </div>
<?php
    $footer = new \SCDS\Footer();
    $footer->render();
  }
} else {
  halt(404);
}
