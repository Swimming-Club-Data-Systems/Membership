<?php

if (!isset($_GET['session']) || !isset($_GET['token'])) halt(404);

$session = \SCDS\Onboarding\Session::retrieve($_GET['session']);

if ($session->token != $_GET['token']) {
  halt(404);
}

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$pagetitle = 'Logged In - Onboarding';

$target = autoUrl("onboarding/go?session=" . urlencode($_GET['session']) . '&token=' . urlencode($_GET['token']));

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
        <h1>Hi <?= htmlspecialchars($user->getFirstName()) ?>!</h1>

        <p class="lead">
          This onboarding session is for a different account to that you are logged in with.
        </p>

        <p>
          If you continue, we will automatically sign you out of your current account.
        </p>

        <p>
          <a href="<?= htmlspecialchars('logout?redirect=' . urlencode($target)) ?>" class="btn btn-success">Continue</a>
        </p>

        <p>
          <a href="<?= htmlspecialchars(autoUrl('')) ?>" class="btn btn-dark">Cancel</a>
        </p>

      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();

?>