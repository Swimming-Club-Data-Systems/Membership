<?php

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$pagetitle = 'Error - Onboarding';

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
      <div class="col-lg-8 col-md-10 text-center">
        <h1>Oops</h1>

        <p class="lead">
          We could not find the onboarding session you were looking for. Your session may have timed out.
        </p>

        <?php if (isset(app()->user)) { ?>

          <p>
            Please visit the <a href="<?= htmlspecialchars(autoUrl('onboarding')) ?>">onboarding home page</a> to find and resume your session.
          </p>

        <?php } else { ?>

          <p>
            Please either <a href="<?= htmlspecialchars(autoUrl('login')) ?>">log into your account</a> or follow the link in your welcome email to resume your session.
          </p>

        <?php } ?>

      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();

?>