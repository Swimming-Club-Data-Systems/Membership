<?php

$setup = false;
$secretKey = null;

use PragmaRX\Google2FA\Google2FA;

$ga2fa = new Google2FA();

if (filter_var(getUserOption($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], "hasGoogleAuth2FA"), FILTER_VALIDATE_BOOLEAN)) {
  $setup = true;
  $secretKey = getUserOption($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], "GoogleAuth2FASecret");
}

$use_white_background = true;
$pagetitle = "Google Authenticator";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <?php if (!$setup) { ?>
        <h1>Setup a time-based one-time password generator app for two-factor authentication</h1>
        <p class="lead">
          You can use an time-based one-time password generator such as iCloud Keychain (Safari 15 onwards), Google Authenticator or Microsoft Authenticator to get your Two-Factor Authentication codes. You can always still get codes by email as a backup if you don't have your device on you.
        </p>
        <p>
          <a href="<?= htmlspecialchars(autoUrl("my-account/googleauthenticator/setup")) ?>" class="btn btn-primary btn-lg">
            Set up app
          </a>
        </p>
      <?php } else { ?>
        <h1>Your Authenticator App Options</h1>
        <p class="lead">
          Welcome to your App Based 2FA Options
        </p>

        <h2>Need to set up your device again?</h2>
        <p>
          We can quickly generate a new token for you to use. This will stop any previous devices that you have set up from working.
        </p>
        <p>
          <a href="<?= autoUrl("my-account/googleauthenticator/setup") ?>" class="btn btn-dark-l btn-outline-light-d">
            Setup Again
          </a>
        </p>

        <h2>Disable Authenticator App</h2>
        <p>
          It's easy to switch back to Email Only Two Factor Authentication at any time. 2FA can not be disabled entirely.
        </p>
        <p>
          <a href="<?= autoUrl("my-account/googleauthenticator/disable") ?>" class="btn btn-dark-l btn-outline-light-d">
            Disable app based
          </a>
        </p>
      <?php } ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
