<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$parentCode = app()->tenant->getKey('ParentCodeOfConduct');

$pagetitle = 'Parent/Guardian Code of Conduct - Onboarding';

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
        <h1 class="text-center">Parent/Guardian Code of Conduct</h1>

        <p class="lead mb-5 text-center">
          Read and agree to the <?= htmlspecialchars(app()->tenant->getName()) ?> Parent/Guardian Code of Conduct.
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

          <?php if ($parentCode) { ?>
            <div id="code_of_conduct">
              <?= getPostContent($parentCode) ?>
            </div>

            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="agree" name="agree" value="1" required>
                <label class="form-check-label" for="agree">
                  I, <?= htmlspecialchars($user->getName()) ?> agree to all of the codes of conduct that are shown to me on this page
                </label>
                <div class="invalid-feedback">
                  Confirm your agreement
                </div>
              </div>
            </div>

            <p>
              By clicking confirm, you acknowledge that you agree to the above code of conduct.
            </p>

            <p>
              <button type="submit" class="btn btn-success">Confirm</button>
            </p>

          <?php } else { ?>

            <p>
              Your club has asked you to agree to their Parent/Guardian Code of Conduct, but we could not find it. Please continue to the next stage.
            </p>

            <p>
              <button type="submit" class="btn btn-success">Continue</button>
            </p>

          <?php } ?>

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