<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$pagetitle = 'Parent help - Onboarding';

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
        <h1 class="text-center">Can you help our club?</h1>

        <p class="lead mb-5 text-center">
          Aquatics clubs across the UK are almost entirely run with the help of volunteers. They play a vital part in providing governance, team administration, technical officiating and other functions that support the delivery of our sports both at grassroots and at national level.
        </p>

        <!-- If has members under 18 -->
        <h2>Do you want to join us as a parent member?</h2>

        <h2>Can you volunteer to help <?= htmlspecialchars(app()->tenant->getName()) ?></h2>
        <!-- For each member we can add volunteering info -->

        <form method="post" class="needs-validation" novalidate>

          <p>
            <button type="submit" class="btn btn-success">Continue</button>
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