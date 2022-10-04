<?php
$fluidContainer = true;
$pagetitle = "Change Password";
include BASE_PATH . "views/header.php";
?>
<div class="container-fluid">
  <div class="row justify-content-between">
    <div class="col-md-3 d-none d-md-block">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/myaccount/ProfileEditorLinks.json'));
      echo $list->render('password');
      ?>
    </div>
    <div class="col-md-9">
      <?php

      if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['PasswordUpdate']) && $_SESSION['TENANT-' . app()->tenant->getId()]['PasswordUpdate']) { ?>
        <div class="alert alert-success">
          <strong>You've successfully changed your password</strong>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['PasswordUpdate']);
      } ?>

      <h1>Change your password</h1>
      <p class="lead">
        A strong password is key to keeping your account secure.
      </p>
      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) {
        echo $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'];
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
      } ?>

      <div class="alert alert-info">
        <p class="mb-0"><strong>We plan to get rid of passwords!</strong></p>
        <p class="mb-0">We've introduced support for passkeys (FIDO2 auth). These allow you to log in with something you have rather than something you remember. Support is widely rolling out to browsers and operating systems throughout 2022.</p>
      </div>

      <form class=" needs-validation" method="post" action="<?= htmlspecialchars(autoUrl("my-account/password")) ?>" novalidate>
        <?= SCDS\CSRF::write() ?>
        <div class="mb-3">
          <label class="form-label" for="current">Confirm your current password</label>
          <input type="password" class="form-control" name="current" id="current" placeholder="Current Password" autocomplete="current-password" required>
          <div class="invalid-feedback">
            Please enter your current password.
          </div>
        </div>

        <div class="row" id="password-row">
          <div class="mb-3 col-md-6">
            <label class="form-label" for="password-1">Password</label>
            <input type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required class="form-control" name="password-1" id="password-1" aria-describedby="pwHelp">
            <small id="pwHelp" class="form-text text-muted">Use 8 characters or more, with at least one lowercase letter, at least one uppercase letter and at least one number</small>
            <div class="invalid-feedback">
              You must provide password that is at least 8 characters long with at least one lowercase letter, at least one uppercase letter and at least one number
            </div>
          </div>
          <div class="mb-3 col-md-6">
            <label class="form-label" for="password-2">Confirm password</label>
            <input type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required class="form-control" name="password-2" id="password-2">
            <div class="invalid-feedback">
              Passwords do not match
            </div>
          </div>
        </div>

        <div class="alert alert-danger d-none" id="pwned-password-warning">
          <p class="mb-0">
            <strong><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Warning</strong>
          </p>
          <p class="mb-0">
            That password has been part of a data breach elsewhere on the internet. You must pick a stronger password.
          </p>
        </div>

        <p><input type="submit" name="submit" id="submit" class="btn btn-success" value="Change password"></p>
      </form>
    </div>
  </div>
</div>

<div id="ajax-options" data-get-pwned-list-ajax-url="<?= htmlspecialchars(autoUrl('ajax-utilities/pwned-password-check')) ?>" data-cross-site-request-forgery-value="<?= htmlspecialchars(\SCDS\CSRF::getValue()) ?>"></div>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->addJS("js/ajax-utilities/pwned-password-check.js");
$footer->useFluidContainer();
$footer->render(); ?>