<?php

use Respect\Validation\Rules\FalseVal;

$target = '';
if (isset($_GET['target'])) {
  $target = $_GET['target'];
}

if (isset($_SESSION['SCDS-SuperUser'])) {
  halt(404);
}

$pagetitle = "Login - Administration Dashboard";

include BASE_PATH . "views/root/head.php";

?>

<div class="container min-vh-100 mb-n3 overflow-auto">
  <div class="row justify-content-center py-3">
    <div class="col-lg-8 col-md-10">
      <div class="">
        <p class="mb-5">
          <a href="<?= htmlspecialchars(autoUrl('')) ?>" class="btn btn-outline-primary btn-outline-light-d">Quit</a>
        </p>

        <h1 class="">Login</h1>
        <p class="mb-5">Sign in to the SCDS Membership Admin Dashboard</p>
      </div>

      <?php if (isset($_SESSION['SCDS-SU-LoginError'])) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>Unknown username or password</strong>
          </p>
          <p class="mb-0">
            Please try again
          </p>
        </div>
      <?php unset($_SESSION['SCDS-SU-LoginError']);
      } ?>

      <?php if (isset($_SESSION['SCDS-SU-Login2FA'])) { ?>

        <form method="post" action="<?= htmlspecialchars(autoUrl("admin/login/2fa")) ?>" name="2faform" id="2faform" class="needs-validation" novalidate>
          <div class="mb-3">
            <label class="form-label" for="auth">Authentication Code</label>
            <input type="number" name="auth" id="auth" class="form-control form-control-lg" required autofocus placeholder="654321" pattern="[0-9]*" inputmode="numeric" min="0" max="999999" step="1">
            <div class="invalid-feedback">
              Please enter a numeric authentication code.
            </div>
          </div>
          <input type="hidden" name="target" value="<?= htmlspecialchars($target) ?>">
          <?= SCDS\CSRF::write() ?>
          <input type="hidden" name="SessionSecurity" value="<?= session_id() ?>">
          <p class="mb-5"><input type="submit" name="verify" id="verify" value="Verify" class="btn btn-lg btn-primary"></p>
        </form>

      <?php } else { ?>
        <form method="post" action="<?= htmlspecialchars(autoUrl("admin/login")) ?>" name="loginform" id="loginform" class="needs-validation" novalidate>
          <div class="mb-3">
            <label class="form-label" for="email-address">Email address</label>
            <input type="email" name="email-address" id="email-address" class="form-control form-control-lg text-lowercase" <?php if (false) { ?> value="<?= htmlspecialchars($username) ?>" <?php } ?> required <?php if (true) { ?>autofocus<?php } ?> placeholder="yourname@example.com" autocomplete="email">
            <div class="invalid-feedback">
              Please enter a valid email address.
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control form-control-lg" required placeholder="Password" <?php if (false) { ?>autofocus<?php } ?> autocomplete="current-password">
            <div class="invalid-feedback">
              Please enter a password.
            </div>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="RememberMe" id="RememberMe" checked aria-describedby="RememberMeHelp">
              <label class="form-check-label" for="RememberMe">Keep me logged in</label>
              <small id="RememberMeHelp" class="form-text text-muted">
                Untick this box if you are using a public or shared computer
              </small>
            </div>
          </div>
          <input type="hidden" name="target" value="<?= htmlspecialchars($target) ?>">
          <?= \SCDS\CSRF::write() ?>
          <input type="hidden" name="SessionSecurity" value="<?= session_id() ?>">
          <p class="mb-5"><input type="submit" name="login" id="login" value="Login" class="btn btn-lg btn-primary"></p>
          <div class="mb-5">
            <p>
              This is not the login page for club users.
            </p>
            <span>
              <a href="<?= htmlspecialchars(autoUrl("admin/login/reset-password")) ?>" class="btn btn-dark-l btn-outline-light-d">
                Forgot password?
              </a>
            </span>
          </div>
        </form>
      <?php } ?>


    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();

?>