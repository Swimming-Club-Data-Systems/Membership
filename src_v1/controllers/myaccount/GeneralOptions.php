<?php

$fluidContainer = true;

$db = app()->db;
$currentUser = app()->user;

$twofaChecked;
if ($currentUser->getUserBooleanOption('Is2FA')) {
  $twofaChecked = " checked ";
}

$trackersChecked;
if ($currentUser->getUserBooleanOption('DisableTrackers')) {
  $trackersChecked = " checked ";
}

$genericThemeChecked;
if ($currentUser->getUserBooleanOption('UsesGenericTheme')) {
  $genericThemeChecked = " checked ";
}

$betasChecked;
if ($currentUser->getUserBooleanOption('EnableBeta')) {
  $betasChecked = " checked ";
}

$notGalaDDChecked;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent" && $currentUser->getUserBooleanOption('GalaDirectDebitOptOut')) {
  $notGalaDDChecked = " checked ";
}

$pagetitle = "General Account Options";
include BASE_PATH . "views/header.php";
$userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
?>
<div class="container-fluid">
  <div class="row justify-content-between">
    <div class="col-md-3 d-none d-md-block">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/myaccount/ProfileEditorLinks.json'));
      echo $list->render('general');
      ?>
    </div>
    <div class="col-md-9">
      <h1>Advanced Account Options</h1>
      <p class="lead">Manage cookies and 2FA.</p>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate']) && $_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate']) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>We've successfully updated your general options</strong>
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate']);
      } ?>

      <form method="post">
        <div class="cell">
          <h2>
            Cookies and Software Settings
          </h2>

          <div class="mb-3">
            <div class="form-switch mb-2">
              <input class="form-check-input" type="checkbox" value="1" id="beta-features" aria-describedby="beta-features-help" name="beta-features" <?= $betasChecked ?>>
              <label class="form-check-label" for="beta-features">Enable beta features</label>
              <small id="beta-features-help" class="form-text text-muted">Help us test new features by opting in to small beta trials.</small>
            </div>
          </div>

          <p class="mb-0">
            <button type="submit" class="btn btn-success">Save</button>
          </p>
        </div>

        <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") { ?>
          <div class="cell">
            <h2>
              Advanced Payment Options
            </h2>
            <div class="mb-3">
              <div class="form-switch mb-2">
                <input class="form-check-input" type="checkbox" value="1" id="gala-dd-opt-out" aria-describedby="gala-dd-opt-out-Help" name="gala-dd-opt-out" <?= $notGalaDDChecked ?>>
                <label class="form-check-label" for="gala-dd-opt-out">Opt out of Direct Debit gala payments</label>
                <small id="gala-dd-opt-out-Help" class="form-text text-muted">This feature is only relevent if your club charges for galas by Direct Debit</small>
              </div>
            </div>
          </div>
        <?php } ?>

        <div class="cell">
          <h2>
            Account Security
          </h2>

          <?php if (filter_var(getUserOption($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], "Is2FA"), FILTER_VALIDATE_BOOLEAN) || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Parent") { ?>

            <p>
              You can use an time-based one-time password generator such as iCloud Keychain (Safari 15 onwards), Google Authenticator or Microsoft Authenticator to get your Two-Factor Authentication codes. You can always still get codes by email as a backup if you don't have your device on you.
            </p>

            <?php if (!filter_var(getUserOption($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], "hasGoogleAuth2FA"), FILTER_VALIDATE_BOOLEAN)) { ?>
              <p>
                <a href="<?= autoUrl("my-account/googleauthenticator") ?>" class="btn btn-primary">
                  Use an authenticator app
                </a>
              </p>
            <?php } else { ?>
              <p>
                You can disable your authenticator app and go back to getting codes by email here.
              </p>

              <p>
                <a href="<?= autoUrl("my-account/googleauthenticator") ?>" class="btn btn-primary">
                  Manage authenticator app
                </a>
              </p>

              <p>
                <a href="<?= autoUrl("my-account/googleauthenticator/disable") ?>" class="btn btn-dark-l btn-outline-light-d">
                  Disable authenticator app
                </a>
              </p>
            <?php } ?>

          <?php } ?>
        </div>

        <div class="cell">
          <h2>
            Your account, your data
            <br><small>Export a copy</small>
          </h2>
          <p>
            Under the General Data Protection Regulation, you can request for free to download all personal data held about you by <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>.
          </p>
          <p>
            <a href="<?= autoUrl("my-account/general/download-personal-data") ?>" class="btn btn-primary">
              Download your data
            </a>
          </p>
          <p>
            You can download the personal data for your swimmers from their respective information pages.
          </p>
        </div>

        <p class="mb-0">
          <button type="submit" class="btn btn-success">Update Details</button>
        </p>
      </form>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render(); ?>