<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$pagetitle = 'Set up your account - Onboarding';

include BASE_PATH . "views/head.php";

?>

<div class="min-vh-100 mb-n3 overflow-auto">
  <div class="bg-light">
    <div class="container">
      <div class="row justify-content-center py-5">
        <div class="col-lg-8 col-md-10">

          <?php if ($logos) { ?>
            <img src="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75.png')) ?>" srcset="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75@2x.png')) ?> 2x, <?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75@3x.png')) ?> 3x" alt="" class="img-fluid d-block mx-auto">
          <?php } else { ?>
            <img src="<?= htmlspecialchars(autoUrl('public/img/corporate/scds.png')) ?>" height="75" width="75" alt="" class="img-fluid d-block mx-auto">
          <?php } ?>

        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row justify-content-center py-5">
      <div class="col-lg-8 col-md-10">
        <h1 class="text-center">Set up your account</h1>

        <p class="lead mb-5 text-center">
          First things first - let's confirm your account details.
        </p>

        <form method="post" class="needs-validation" novalidate>

          <?php if (isset($_SESSION['FormError']) && $_SESSION['FormError']) { ?>
            <div class="alert alert-danger">
              <p class="mb-0">
                <strong>Please check your details</strong>
              </p>
            </div>
          <?php unset($_SESSION['FormError']);
          } ?>

          <!-- <h2>Basic details</h2> -->
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="first-name" class="form-label">First name</label>
                <input type="text" class="form-control" id="first-name" name="first-name" value="<?= htmlspecialchars($user->getFirstName()) ?>" required>
                <div class="invalid-feedback">
                  Please enter your first name
                </div>
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="last-name" class="form-label">Last name</label>
                <input type="text" class="form-control" id="last-name" name="last-name" value="<?= htmlspecialchars($user->getLastName()) ?>" required>
                <div class="invalid-feedback">
                  Please enter your last name
                </div>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="email-address" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email-address" name="email-address" value="<?= htmlspecialchars($user->getEmail()) ?>" required>
            <div class="invalid-feedback">
              Please enter your email address
            </div>
          </div>

          <div class="mb-3">
            <label for="phone-number" class="form-label">Mobile number</label>
            <input type="tel" class="form-control" id="phone-number" name="phone-number" value="<?= htmlspecialchars($user->getMobile()) ?>" required>
            <div class="invalid-feedback">
              Please enter your mobile phone number
            </div>
          </div>

          <!-- <h2>Choose a password</h2> -->
          <div class="row" id="password-form-row">
            <div class="col-sm">
              <div class="mb-3">
                <label class="form-label" for="password-1">Create a password</label>
                <input type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" class="form-control" id="password-1" name="password-1" autocomplete="new-password" required aria-describedby="pwHelp">
                <small id="pwHelp" class="form-text text-muted">
                  Use 8 characters or more, with at least one lowercase letter, at least one uppercase letter and at least one number
                </small>
                <div class="invalid-feedback">
                  You must provide password that is at least 8 characters long with at least one lowercase letter, at least one uppercase letter and at least one number
                </div>
              </div>
            </div>

            <div class="col-sm">
              <div class="mb-3">
                <label class="form-label" for="password-2">Confirm your password</label>
                <input type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" class="form-control" id="password-2" name="password-2" autocomplete="new-password" required aria-describedby="pwConfirmHelp">
                <small id="pwConfirmHelp" class="form-text text-muted">Repeat your password</small>
                <div class="invalid-feedback" id="password-2-invalid-feedback">
                  Passwords do not match
                </div>
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

          <p>
            <button type="submit" class="btn btn-success">Confirm</button>
          </p>

        </form>

      </div>
    </div>
  </div>
</div>

<div id="ajax-options" data-get-pwned-list-ajax-url="<?= htmlspecialchars(autoUrl('ajax-utilities/pwned-password-check')) ?>" data-cross-site-request-forgery-value="<?= htmlspecialchars(\SCDS\CSRF::getValue()) ?>"></div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('js/NeedsValidation.js');
$footer->addJS("js/ajax-utilities/pwned-password-check.js");
$footer->render();

?>