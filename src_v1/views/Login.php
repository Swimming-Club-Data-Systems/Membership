<?php

$target = "";
if (isset($_GET['target'])) {
  $target = $_GET['target'];
}

$pagetitle = "Login";

$errorState = null;
$username = '';

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) {
  $errorState = $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'];
}

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['EnteredUsername'])) {
  $username = $_SESSION['TENANT-' . app()->tenant->getId()]['EnteredUsername'];
}

$logos = app()->tenant->getKey('LOGO_DIR');

include BASE_PATH . "views/head.php";

?>

<div class="container min-vh-100 mb-n3 overflow-auto">
  <!-- <h1 class="mb-5">Sign in using your <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> Account</h1> -->
  <div class="row justify-content-center py-3">
    <div class="col-lg-8 col-md-10">

      <p class="mb-5">
        <a href="<?= htmlspecialchars(autoUrl('')) ?>" class="btn btn-outline-primary btn-outline-light-d">Quit</a>
      </p>

      <div class="row align-items-center">
        <div class="col order-2 order-md-1">
          <h1 class="">Login</h1>
          <p class="">Sign in to <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?></p>
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
      <!--
      <div class="alert alert-warning">
        <strong>Overnight System Maintenance</strong> <br>
        Our systems will not be sending emails between 23:00 on Friday 31 August and 15:00 on Saturday 1 September
      </div>
      -->
      <?php if ($errorState) { ?>
        <div class="alert alert-danger">
          <strong>Your details were incorrect</strong> <br>
          Please try again
          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStateLSVMessage'])) {
            echo $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStateLSVMessage'];
            unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStateLSVMessage']);
          } ?>
        </div>
      <?php } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorAccountLocked']) && $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorAccountLocked']) { ?>
        <div class="alert alert-danger">
          <strong>Your account has been locked due to a number of failed login attempts</strong> <br>
          Please <a href="<?= htmlspecialchars(autoUrl("resetpassword")) ?>" class="alert-link">reset your password</a> in order to continue
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorAccountLocked']);
      } ?>

      <form method="post" action="<?= htmlspecialchars(autoUrl("login")) ?>" name="loginform" id="loginform" class="needs-validation" novalidate>
        <div class="mb-3">
          <label class="form-label" for="email-address">Email address</label>
          <input type="email" name="email-address" id="email-address" class="form-control form-control-lg text-lowercase" <?php if ($errorState) { ?> value="<?= htmlspecialchars($username) ?>" <?php } ?> required <?php if (!$username) { ?>autofocus<?php } ?> placeholder="yourname@example.com" autocomplete="email">
          <div class="invalid-feedback">
            Please enter a valid email address.
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <input type="password" name="password" id="password" class="form-control form-control-lg" required placeholder="Password" <?php if ($username) { ?>autofocus<?php } ?> autocomplete="current-password">
          <div class="invalid-feedback">
            Please enter a password.
          </div>
        </div>
        <div class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="RememberMe" id="RememberMe" checked aria-describedby="RememberMeHelp" value="1">
            <label class="form-check-label" for="RememberMe">Keep me logged in</label>
            <small id="RememberMeHelp" class="form-text text-muted">
              Untick this box if you are using a public or shared computer
            </small>
          </div>
        </div>
        <input type="hidden" name="target" value="<?= htmlspecialchars($target) ?>">
        <?= \SCDS\CSRF::write() ?>
        <input type="hidden" name="SessionSecurity" value="<?= htmlspecialchars(session_id()) ?>">
        <p class="mb-5"><input type="submit" name="login" id="login" value="Login" class="btn btn-lg btn-primary"></p>
        <div class="mb-5">
          <p>
            New member? Your club will create an account for you and send you a link to get started.
          </p>
          <span>
            <a href="<?= htmlspecialchars(autoUrl("resetpassword")) ?>" class="btn btn-dark">
              Forgot password?
            </a>
          </span>
        </div>
      </form>

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

?>