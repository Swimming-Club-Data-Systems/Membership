<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$termsId = app()->tenant->getKey('TermsAndConditions');

$pagetitle = 'Terms and Conditions of Membership Agreement - Onboarding';

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
        <h1 class="text-center">Terms and Conditions of Membership Agreement</h1>

        <p class="lead mb-5 text-center">
          Read and agree to the terms and conditions of <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> membership.
        </p>

        <form method="post" class="needs-validation" novalidate>

          <?php if (isset($_SESSION['FormError']) && $_SESSION['FormError']) { ?>
            <div class="alert alert-danger">
              <p class="mb-0">
                <strong>An error occurred when we tried to save the changes</strong>
              </p>
            </div>
          <?php unset($_SESSION['FormError']);
          } ?>

          <?php if ($termsId) { ?>
            <div id="ts-and_cs">
              <?= getPostContent($termsId) ?>
            </div>

            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="agree" name="agree" value="1" required>
                <label class="form-check-label" for="agree">
                  I, <?= htmlspecialchars($user->getName()) ?> agree to the terms shown to me on this page
                </label>
                <div class="invalid-feedback">
                  Confirm your agreement
                </div>
              </div>
            </div>

            <p>
            By clicking confirm, you acknowledge that you agree to the above data protection agreement and privacy policy.
          </p>
          <?php } else { ?>
            <p>
              Your club has not set a terms and conditions policy.
            </p>
          <?php } ?>

          <p>
            <button type="submit" class="btn btn-success">Confirm</button>
          </p>

        </form>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('js/NeedsValidation.js');
$footer->render();

?>