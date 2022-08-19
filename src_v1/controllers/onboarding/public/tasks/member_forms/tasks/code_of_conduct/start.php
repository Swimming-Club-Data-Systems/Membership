<?php

$db = app()->db;

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Member::stagesOrder();

// Get member
$onboardingMember = \SCDS\Onboarding\Member::retrieveById($id);

$member = $onboardingMember->getMember();

// Work out which forms we need to fill out
$db = app()->db;

$getSquads = $db->prepare("SELECT SquadID, SquadName, SquadCoC FROM squads INNER JOIN squadMembers ON squadMembers.Squad = squads.SquadID WHERE squadMembers.Member = ?");
$getSquads->execute([
  $member->getId(),
]);

$codes = [];
$without = [];

while ($squad = $getSquads->fetch(PDO::FETCH_ASSOC)) {
  if (isset($squad['SquadCoC']) && $squad['SquadCoC'] != "" && ((int) $squad['SquadCoC']) != 0) {
    if (!isset($codes[(string) $squad['SquadCoC']])) {
      $codes[(string) $squad['SquadCoC']] = [];
    }

    $codes[(string) $squad['SquadCoC']][] = [
      'squad' => $squad['SquadID'],
      'name' => $squad['SquadName']
    ];
  } else {
    $without[] = [
      'squad' => $squad['SquadID'],
      'name' => $squad['SquadName'],
    ];
  }
}

$numFormatter = new NumberFormatter('en-GB', NumberFormatter::SPELLOUT);

$pagetitle = 'Code of Conduct - ' . htmlspecialchars($member->getFullName()) . ' - Onboarding';

include BASE_PATH . "views/head.php";

?>

<div class="min-vh-100 mb-n3 overflow-auto" s>
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
        <h1 class="text-center">Code of Conduct</h1>

        <p class="lead mb-5 text-center">
          Provide medical details for <?= htmlspecialchars($member->getFullName()) ?>.
        </p>

        <form method="post" class="needs-validation" novalidate>

          <?php if (isset($_SESSION['FormError']) && $_SESSION['FormError']) { ?>
            <div class="alert alert-danger">
              <p class="mb-0">
                <strong>There was a problem there.</strong>
              </p>
              <p class="mb-0">
                Please check you ticked all the boxes.
              </p>
            </div>
          <?php unset($_SESSION['FormError']);
          } ?>

          <?php if ($member->getAge() < 18) { ?>
            <p>
              <strong>
                You must ensure that <?= htmlspecialchars($member->getForename()) ?> is present to agree to the code of conduct before you continue as they must read and agree to it.
              </strong>
            </p>

            <p>
              Please help explain the code of conduct if <?= htmlspecialchars($member->getForename()) ?> does not understand it.
            </p>
          <?php } ?>

          <?php if (sizeof($codes) > 1) { ?>
            <p>
              There are <?= htmlspecialchars($numFormatter->format(sizeof($codes))) ?> codes of conduct to agree to.
            </p>
          <?php } ?>

          <?php foreach ($codes as $code => $squads) { ?>

            <div class="card mb-3">
              <div class="card-header">
                <h2 class="card-title">Code of Conduct</h2>
                <p class="mb-0">
                  <?php $comma = false; ?>
                  For <?php foreach ($squads as $squad) { ?><?php if ($comma) { ?>, <?php } ?><?= htmlspecialchars($squad['name']) ?><?php $comma = true;
                                                                                                                                    } ?>
                </p>
              </div>
              <div class="card-body">
                <div class="code-of-conduct">
                  <?= getPostContent((int) $code) ?>
                </div>
              </div>
            </div>

          <?php } ?>

          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="agree" name="agree" value="1" required>
              <label class="form-check-label" for="agree">
                I, <?= htmlspecialchars($member->getFullName()) ?> agree to all of the codes of conduct that are shown to me on this page
              </label>
              <div class="invalid-feedback">
                Confirm your agreement
              </div>
            </div>
          </div>

          <?php if ($member->getAge() < 18) { ?>
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="parent-agree" name="parent-agree" value="1" required>
                <label class="form-check-label" for="parent-agree">
                  I, <?= htmlspecialchars($user->getFullName()) ?> agree to all of the codes of conduct that are shown to <?= htmlspecialchars($member->getForename()) ?> on this page and that I have helped explain them if required
                </label>
                <div class="invalid-feedback">
                  Confirm your agreement
                </div>
              </div>
            </div>
          <?php } ?>

          <?= SCDS\CSRF::write() ?>

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
