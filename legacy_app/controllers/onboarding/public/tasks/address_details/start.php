<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$pagetitle = 'Your address - Onboarding';

$addr = [];
$json = $user->getUserOption('MAIN_ADDRESS');
if ($json != null) {
  $addr = json_decode($json);
}

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
        <h1 class="text-center">Tell us your address</h1>

        <p class="lead mb-5 text-center">
          First things first - let's confirm your account details.
        </p>

        <?php if (isset($_SESSION['FormError']) && $_SESSION['FormError']) { ?>
          <div class="alert alert-danger">
            <p class="mb-0">
              <strong>An error occurred when we tried to save the changes</strong>
            </p>
          </div>
        <?php unset($_SESSION['FormError']);
        } ?>

        <?php if (isset($addr->streetAndNumber)) { ?>
          <h2>Your current address is</h2>
          <address>
            <?= htmlspecialchars($addr->streetAndNumber) ?><br>
            <?php if (isset($addr->flatOrBuilding)) { ?>
              <?= htmlspecialchars($addr->flatOrBuilding) ?><br>
            <?php } ?>
            <?= htmlspecialchars(mb_strtoupper($addr->city)) ?><br>
            <?= htmlspecialchars(mb_strtoupper($addr->postCode)) ?><br>
          </address>

          <h2>Edit address</h2>
        <?php } else { ?>
          <h2>Add address</h2>
        <?php } ?>

        <p>You must use a UK address or a British Forces address.</p>

        <form method="post" class="needs-validation" novalidate>
          <div class="mb-3">
            <label class="form-label" for="street-and-number">Address line 1 (street and number)</label>
            <input class="form-control" name="street-and-number" id="street-and-number" type="text" autocomplete="address-line1" <?php if (isset($addr->streetAndNumber)) { ?>value="<?= htmlspecialchars($addr->streetAndNumber) ?>" <?php } ?> required>
            <div class="invalid-feedback">
              Please enter address line 1
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="flat-building">Address line 2 (optional)</label>
            <input class="form-control" name="flat-building" id="flat-building" type="text" autocomplete="address-line2" <?php if (isset($addr->flatOrBuilding)) { ?>value="<?= htmlspecialchars($addr->flatOrBuilding) ?>" <?php } ?>>
          </div>

          <div class="mb-3">
            <label class="form-label" for="town-city">Town/City</label>
            <input class="form-control" name="town-city" id="town-city" type="text" autocomplete="address-level2" <?php if (isset($addr->city)) { ?>value="<?= htmlspecialchars($addr->city) ?>" <?php } ?> required>
            <div class="invalid-feedback">
              Please enter a town or city
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="county-province">County</label>
            <input class="form-control" name="county-province" id="county-province" type="text" autocomplete="address-level1" <?php if (isset($addr->county)) { ?>value="<?= htmlspecialchars($addr->county) ?>" <?php } ?> required>
            <div class="invalid-feedback">
              Please enter a ceremonial or traditional county
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="post-code">Post Code</label>
            <input class="form-control" name="post-code" id="post-code" type="text" autocomplete="postal-code" <?php if (isset($addr->postCode)) { ?>value="<?= htmlspecialchars($addr->postCode) ?>" <?php } ?> required>
            <div class="invalid-feedback">
              Please enter post code
            </div>
          </div>

          <p>
            <button type="submit" class="btn btn-success">Confirm</button>
          </p>
      </div>
    </div>

    </form>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('js/NeedsValidation.js');
$footer->render();

?>