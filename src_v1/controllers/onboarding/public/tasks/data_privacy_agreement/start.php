<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$db = app()->db;

$privacy = app()->tenant->getKey('PrivacyPolicy');
$Extra = new ParsedownExtra();
$Extra->setSafeMode(true);
$search  = array("\n##### ", "\n#### ", "\n### ", "\n## ", "\n# ");
$replace = array("\n###### ", "\n###### ", "\n##### ", "\n#### ", "\n### ");

$privacyPolicy = null;
if ($privacy != null && $privacy != "") {
  $privacyPolicy = $db->prepare("SELECT Content FROM posts WHERE ID = ?");
  $privacyPolicy->execute([$privacy]);
  $privacyPolicy = str_replace($search, $replace, $privacyPolicy->fetchColumn());
  if ($privacyPolicy[0] == '#') {
    $privacyPolicy = '##' . $privacyPolicy;
  }
}

$pagetitle = 'Data Protection Agreement - Onboarding';

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
        <h1 class="text-center">Data Protection Agreement</h1>

        <p class="lead mb-5 text-center">
          Read and agree to our Privacy Policy.
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

          <h2>Data Protection Statement</h2>
          <p>
            I understand that, in compliance with the UK Data Protection Act (which incorporates the pre-Brexit General Data Protection Regulation), all efforts will be made to ensure that information is accurate, kept up to date and secure, and that it is used only in connection with the purposes of <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>. Information will be disclosed only to those members of the club for whom it is appropriate, and relevant officers of the Amateur Swimming Association (Swim England) or British Swimming. Information will not be kept once a person has left the club.
          </p>

          <h2><?= htmlspecialchars(app()->tenant->getName()) ?> Privacy Policy</h2>
          <?php if ($privacyPolicy != null) { ?>
            <?= $Extra->text($privacyPolicy) ?>
          <?php } else { ?>
            <p>
              <strong>YOUR CLUB HAS NOT SET UP A PRIVACY POLICY. PLEASE DO NOT PROCEED.</strong>
            </p>
          <?php } ?>

          <h2>myswimmingclub.uk (SCDS) Privacy Policy</h2>
          <p>
            Use of your <?= htmlspecialchars(app()->tenant->getName()) ?> account is also subject to the terms of service and privacy policies of Swimming Club Data Systems. SCDS provides this platform on behalf of your club. Your club remains the data controller. SCDS is a data processor.
          </p>

          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="agree" name="agree" value="1" required>
              <label class="form-check-label" for="agree">
                I, <?= htmlspecialchars($user->getName()) ?> agree to the privacy terms shown to me on this page
              </label>
              <div class="invalid-feedback">
                Confirm your agreement
              </div>
            </div>
          </div>

          <p>
            By clicking confirm, you acknowledge that you agree to the above data protection agreement and privacy policy.
          </p>

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