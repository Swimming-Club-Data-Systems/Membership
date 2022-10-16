<?php

$setup = false;
$secretKey = null;

$reset = false;
if (filter_var(getUserOption($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], "hasGoogleAuth2FA"), FILTER_VALIDATE_BOOLEAN)) {
  $reset = true;
}

use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['G2FAKey'])) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['G2FAKey'] = $google2fa->generateSecretKey(32);
}

$pagetitle = "Generate Key";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h1>Time-based one-time password setup</h1>
      <?php if ($reset) { ?>
        <p>
          Hi there. We're going to reset the codes you use for your time-based one-time password app. As soon as you confirmed setup below, any devices you've already set up will stop working. This is essential to protect your security.
        </p>
      <?php } ?>

      <h2>Set up your app</h2>
      <p class="lead">
        Choose a setup method
      </p>

      <div class="row">
        <div class="col-md-6 pb-3">
          <div class="card card-body h-100 d-grid">
            <div>
              <h3>Automatic Setup</h3>

              <?php if (isset($_SESSION['Browser']['Name']) && $_SESSION['Browser']['Name'] == 'Safari' && isset($_SESSION['Browser']['Version']) && (int) $_SESSION['Browser']['Version'] >= 15) { ?>
                <p>
                  <a href="apple-<?= htmlspecialchars($google2fa->getQRCodeUrl(app()->tenant->getKey('CLUB_NAME'), $_SESSION['TENANT-' . app()->tenant->getId()]['EmailAddress'], $_SESSION['TENANT-' . app()->tenant->getId()]['G2FAKey'])) ?>">Set up code with iCloud Keychain</a>.
                </p>

                <p class="mb-0">
                  <a href="<?= htmlspecialchars($google2fa->getQRCodeUrl(app()->tenant->getKey('CLUB_NAME'), $_SESSION['TENANT-' . app()->tenant->getId()]['EmailAddress'], $_SESSION['TENANT-' . app()->tenant->getId()]['G2FAKey'])) ?>">Set up code with another app</a>.
                </p>
              <?php } else { ?>

                <p class="mb-0">
                  <a href="<?= htmlspecialchars($google2fa->getQRCodeUrl(app()->tenant->getKey('CLUB_NAME'), $_SESSION['TENANT-' . app()->tenant->getId()]['EmailAddress'], $_SESSION['TENANT-' . app()->tenant->getId()]['G2FAKey'])) ?>">Set up time-based one-time password app</a>.
                </p>
              <?php } ?>
            </div>
          </div>
        </div>
        <div class="col-md-6 pb-3">
          <div class="card card-body h-100 d-grid">
            <h3>Scan Code</h3>
            <?php
            $qr_url = urlencode($google2fa->getQRCodeUrl(app()->tenant->getKey('CLUB_NAME'), $_SESSION['TENANT-' . app()->tenant->getId()]['EmailAddress'], $_SESSION['TENANT-' . app()->tenant->getId()]['G2FAKey']));
            ?>
            <div>
              <img src="<?= htmlspecialchars(autoUrl("services/qr-generator?size=200&margin=0&text=" . $qr_url)) ?>" srcset="<?= htmlspecialchars(autoUrl("services/qr-generator?size=400&margin=0&text=" . $qr_url)) ?> 2x, <?= htmlspecialchars(autoUrl("services/qr-generator?size=600&margin=0&text=" . $qr_url)) ?> 3x" class="img-fluid mb-3">
            </div>
            <p class="mb-0">
              Scan this QR Code with your Authenticator App.
            </p>
          </div>
        </div>
      </div>

      <p>
        If your app does not support these setup methods, please use the code below;<br> <span class="font-monospace"><?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['G2FAKey']) ?></span>
      </p>

      <h2>Confirm Setup</h2>
      <p>
        We're going to ask you to enter the code shown in your app. This verifies your app has been set up correctly.
      </p>
      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['G2FA_VerifyError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['G2FA_VerifyError']) { ?>
        <div class="alert alert-danger">
          <strong>The code you entered was not valid</strong>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['G2FA_VerifyError']);
      } ?>
      <form method="post" class="needs-validation" novalidate>
        <div class="mb-3">
          <label class="form-label" for="verify">Verify Code</label>
          <input type="text" class="form-control" id="verify" name="verify" aria-describedby="verifyHelp" placeholder="654321" pattern="[0-9]*" inputmode="numeric" min="0" max="999999" step="1" required>
          <div class="invalid-feedback">
            You must enter a valid code
          </div>
          <small id="verifyHelp" class="form-text text-muted">This code is shown in your app.</small>
        </div>
        <p>
          <button class="btn btn-primary" type="submit">Verify</button>
        </p>
      </form>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->render();
