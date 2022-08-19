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

// Get permission details
$get = $db->prepare("SELECT Website, Social, Noticeboard, FilmTraining, ProPhoto FROM memberPhotography WHERE MemberID = ?");
$get->execute([
  $member->getId(),
]);
$permissions = $get->fetch(PDO::FETCH_ASSOC);

if (!$permissions) {
  $permissions = [];
}

$pagetitle = 'Photography Permissions - ' . htmlspecialchars($member->getFullName()) . ' - Onboarding';

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
        <h1 class="text-center">Photography and filming consent</h1>

        <p class="lead mb-5 text-center">
          Provide medical details for <?= htmlspecialchars($member->getFullName()) ?>.
        </p>

        <?php if ($member->getAge() >= 18) { ?>
          <div class="alert alert-info">
            <p class="mb-0">
              <strong>This page is only for members under 18</strong>
            </p>
            <p class="mb-0">
              You're seeing this because <?= htmlspecialchars($member->getForename()) ?> was under 18 when this onboarding session was created or a member of club staff has explicitly requested it.
            </p>
          </div>
        <?php } ?>

        <form method="post">

          <p>
            You should complete this form after <a href="https://www.swimming.org/swimengland/wavepower-child-safeguarding-for-clubs/">reading the Swim England Photography and Filming guidance contained in Wavepower (opens in new tab)</a> and the <a href="<?= htmlspecialchars(autoUrl('privacy')) ?>" target="_blank"><?= htmlspecialchars($tenant->getName()) ?> Privacy Policy</a>.
          </p>

          <p>
            <?= htmlspecialchars($tenant->getName()) ?> may wish to take photographs or film individual or groups of members under the age of 18 that may include <?= htmlspecialchars($member->getFullName()) ?> during their membership of <?= htmlspecialchars($tenant->getName()) ?>. All photographs and filming and all use of such images will be in accordance with the Swim England Photography and Filming Guidance and <?= htmlspecialchars($tenant->getName()) ?>'s Privacy Policy.
          </p>

          <p>
            <?= htmlspecialchars($tenant->getName()) ?> will take all reasonable steps to ensure images and any footage is being used solely for their intended purpose and not kept for any longer than is necessary for that purpose. If you have any concerns or questions about how they are being used please contact the Welfare Officer to discuss this further.
          </p>

          <p>
            As a parent/guardian please complete the below in respect of <?= htmlspecialchars($member->getFullName()) ?>. We encourage all parents/guardians to discuss and explain their choices with their child/ren. Please note that either you or your child can withdraw consent or object to a particular type of use by notifying the Welfare Officer at any time and changing the consents given from within your club account.
          </p>

          <p>
            As the parent/guardian of <?= htmlspecialchars($member->getFullName()) ?> I am happy for:
          </p>

          <div class="card mb-3">
            <div class="card-header">
              Media uses
            </div>
            <div class="list-group list-group-flush">
              <div class="list-group-item">
                <div class="mb-2">
                  <?= htmlspecialchars($member->getForename()) ?>'s photograph to be used on the <?= htmlspecialchars($tenant->getName()) ?> website.
                </div>

                <div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="website-yes" name="website" value="1" <?php if (isset($permissions['Website']) && $permissions['Website']) { ?>checked<?php } ?>>
                    <label class="form-check-label" for="website-yes">Yes</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="website-no" name="website" value="0" <?php if (!(isset($permissions['Website']) && $permissions['Website'])) { ?>checked<?php } ?>>
                    <label class="form-check-label" for="website-no">No</label>
                  </div>
                </div>
              </div>

              <div class="list-group-item">
                <div class="mb-2">
                  <?= htmlspecialchars($member->getForename()) ?>'s photograph to be used on <?= htmlspecialchars($tenant->getName()) ?> social media platform/s.
                </div>

                <div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="social-yes" name="social" value="1" <?php if (isset($permissions['Social']) && $permissions['Social']) { ?>checked<?php } ?>>
                    <label class="form-check-label" for="social-yes">Yes</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="social-no" name="social" value="0" <?php if (!(isset($permissions['Social']) && $permissions['Social'])) { ?>checked<?php } ?>>
                    <label class="form-check-label" for="social-no">No</label>
                  </div>
                </div>
              </div>

              <div class="list-group-item">
                <div class="mb-2">
                  <?= htmlspecialchars($member->getForename()) ?>'s photograph to be used within other printed publications such as newspaper articles about <?= htmlspecialchars($tenant->getName()) ?>.
                </div>

                <div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="noticeboard-yes" name="noticeboard" value="1" <?php if (isset($permissions['Noticeboard']) && $permissions['Noticeboard']) { ?>checked<?php } ?>>
                    <label class="form-check-label" for="noticeboard-yes">Yes</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="noticeboard-no" name="noticeboard" value="0" <?php if (!(isset($permissions['Noticeboard']) && $permissions['Noticeboard'])) { ?>checked<?php } ?>>
                    <label class="form-check-label" for="noticeboard-no">No</label>
                  </div>
                </div>
              </div>

              <div class="list-group-item">
                <div class="mb-2">
                  <?= htmlspecialchars($member->getForename()) ?>'s photograph to be taken by a professional photographer employed by <?= htmlspecialchars($tenant->getName()) ?> as the official photographer at competitions, galas and other organisational events.
                </div>

                <div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="pro-photo-yes" name="pro-photo" value="1" <?php if (isset($permissions['ProPhoto']) && $permissions['ProPhoto']) { ?>checked<?php } ?>>
                    <label class="form-check-label" for="pro-photo-yes">Yes</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="pro-photo-no" name="pro-photo" value="0" <?php if (!(isset($permissions['ProPhoto']) && $permissions['ProPhoto'])) { ?>checked<?php } ?>>
                    <label class="form-check-label" for="pro-photo-no">No</label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card mb-3">
            <div class="card-header">
              Training uses (training videos to be deleted once the relevant training is complete)
            </div>
            <div class="list-group list-group-flush">
              <div class="list-group-item">
                <div class="mb-2">
                  <?= htmlspecialchars($member->getForename()) ?> to be filmed by <?= htmlspecialchars($tenant->getName()) ?> for training purposes.
                </div>

                <div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="film-training-yes" name="film-training" value="1" <?php if (isset($permissions['FilmTraining']) && $permissions['FilmTraining']) { ?>checked<?php } ?>>
                    <label class="form-check-label" for="film-training-yes">Yes</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="film-training-no" name="film-training" value="0" <?php if (!(isset($permissions['FilmTraining']) && $permissions['FilmTraining'])) { ?>checked<?php } ?>>
                    <label class="form-check-label" for="film-training-no">No</label>
                  </div>
                </div>
              </div>
            </div>
          </div>

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
$footer->render();
