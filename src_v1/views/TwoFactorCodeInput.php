<?php

$pagetitle = "Two Factor Authentication";

$do_random_2FA = filter_var(getUserOption($_SESSION['TENANT-' . app()->tenant->getId()]['2FAUserID'], "IsSpotCheck2FA"), FILTER_VALIDATE_BOOLEAN);

$errorState = false;

$target = "";
if (isset($_GET['target'])) {
  $target = $_GET['target'];
}

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) {
  $errorState = $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'];
}

$logos = app()->tenant->getKey('LOGO_DIR');

include BASE_PATH . "views/head.php";

?>

<div class="container min-vh-100 mb-n3 overflow-auto">
  <div class="row justify-content-center py-3">
    <div class="col-lg-8 col-md-10">

      <p class="mb-5">
        <a href="<?= htmlspecialchars(autoUrl("2fa/exit")) ?>" class="btn btn-outline-primary">Cancel</a>
      </p>

      <div class="row align-items-center">
        <div class="col order-2 order-md-1">
          <h1>Enter your authentication code</h1>
          <p class="">To continue signing in to <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?></p>
        </div>
        <div class="col-12 col-md-auto order-1 order-md-2">
          <?php if ($logos) { ?>
            <img src="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75.png')) ?>" srcset="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75@2x.png')) ?> 2x, <?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75@3x.png')) ?> 3x" alt="" class="img-fluid">
          <?php } else { ?>
            <img src="<?= htmlspecialchars(autoUrl('public/img/corporate/scds.png')) ?>" height="75" width="75" alt="" class="img-fluid">
          <?php } ?>
          <div class="mb-4 d-md-none"></div>
        </div>
      </div>
      <div class="mb-4 d-md-none"></div>
      <div class="mb-5 d-none d-md-block"></div>
      <?php if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_GOOGLE']) || $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_GOOGLE'] !== true) { ?>
        <p class="lead mb-5">
          We've just sent you an authentication code by email. Please type this code below.
        </p>
      <?php } else { ?>
        <p class="lead mb-5">
          Enter the authentication code from your Authenticator App.
        </p>
      <?php } ?>
      <?php if (isset($do_random_2FA) && $do_random_2FA) { ?>
        <p class="mb-5">We've asked you to do this as part of a random security spot check.</p>
      <?php } ?>
      <!--
      <div class="alert alert-warning">
        <strong>Overnight System Maintenance</strong> <br>
        Our systems will not be sending emails between 23:00 on Friday 31 August and 15:00 on Saturday 1 September
      </div>
      -->
      <?php if (isset($errorState) && $errorState) { ?>
        <div class="alert alert-danger">
          <strong>Your authentication code was incorrect</strong> <br>
          Please try again
          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStateLSVMessage'])) {
            echo $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStateLSVMessage'];
            unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStateLSVMessage']);
          } ?>
        </div>
      <?php } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_RESEND']) && $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_RESEND']) { ?>
        <div class="alert alert-success">
          <p class="mb-0"><strong>We have successfully sent your email</strong></p>
          <p class="mb-0">Please now check your inbox. It may take a moment to receive the email.</p>
        </div>
      <?php } ?>

      <form method="post" action="<?= autoUrl("2fa") ?>" name="2faform" id="2faform" class="needs-validation" novalidate>
        <div class="mb-3">
          <label class="form-label" for="auth">Authentication Code</label>
          <input type="number" name="auth" id="auth" class="form-control form-control-lg" required autofocus placeholder="654321" pattern="[0-9]*" inputmode="numeric" min="0" max="999999" step="1" autocomplete="one-time-code">
          <div class="invalid-feedback">
            Please enter a numeric authentication code.
          </div>
        </div>
        <input type="hidden" name="target" value="<?= htmlspecialchars($target) ?>">

        <div class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="RememberMe" id="RememberMe" <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['2FAUserRememberMe']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['2FAUserRememberMe'])) { ?>checked<?php } ?> aria-describedby="RememberMeHelp" value="1">
            <label class="form-check-label" for="RememberMe">Keep me logged in</label>
            <small id="RememberMeHelp" class="form-text text-muted">
              Untick this box if you are using a public or shared computer
            </small>
          </div>
        </div>

        <?php if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_GOOGLE']) || $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_GOOGLE'] !== true) { ?>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="setup-time-based-codes" id="setup-time-based-codes" aria-describedby="setup-time-based-codes-help">
              <label class="form-check-label" for="setup-time-based-codes">Set up an authenticator app</label>
              <small id="setup-time-based-codes-help" class="form-text text-muted">
                Set up a time-based one-time password authenticator app, such as iCloud Keychain (Safari 15 onwards), Google Authenticator or Microsoft Authenticator to get your Two-Factor Authentication codes, so that you don't need to wait for an email from us. You can always still get codes by email if you don't have your phone with you.
              </small>
            </div>
          </div>
        <?php } ?>

        <?= SCDS\CSRF::write() ?>
        <input type="hidden" name="SessionSecurity" value="<?= session_id() ?>">
        <p class="mb-5"><input type="submit" name="verify" id="verify" value="Verify" class="btn btn-lg btn-primary"></p>
      </form>

      <p class="mb-5">
        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_GOOGLE']) && $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_GOOGLE']) { ?>
          <a href="<?= htmlspecialchars(autoUrl("2fa/resend?target=" . urlencode($target))) ?>" class="btn btn-dark">Get code by email</a>
        <?php } else { ?>
          <a href="<?= htmlspecialchars(autoUrl("2fa/resend?target=" . urlencode($target))) ?>" class="btn btn-dark">Resend Email</a>
        <?php } ?>
      </p>

      <p>
        Need help? <a href="<?= htmlspecialchars(autoUrl('about')) ?>">Get support from <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?></a>.
      </p>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs("public/js/NeedsValidation.js");
$footer->render();

unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
unset($_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_RESEND']);

?>