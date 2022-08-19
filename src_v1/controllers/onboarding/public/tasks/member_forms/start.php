<?php

$db = app()->db;

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Member::stagesOrder();

$displayed = false;

// Get the members
$getMembers = $db->prepare("SELECT MForename firstName, MSurname lastName, MemberID member, stages FROM onboardingMembers INNER JOIN members ON onboardingMembers.member = members.MemberID WHERE onboardingMembers.session = ? ORDER BY MForename, MSurname;");
$getMembers->execute([
  $session->id,
]);
$member = $getMembers->fetch(PDO::FETCH_OBJ);

$pagetitle = 'Member information - Onboarding';

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
        <h1 class="text-center">Member information</h1>

        <p class="lead mb-5 text-center">
          We now need you to provide details for each member connected to this account.
        </p>

        <?php if ($member) { ?>
          <?php do { ?>
            <?php $onboardingMember = \SCDS\Onboarding\Member::retrieve($member->member, $session->id); ?>
            <?php $stages = $onboardingMember->stages; ?>
            <h2><?= htmlspecialchars($member->firstName . ' ' . $member->lastName) ?></h2>
            <?php if (true) { ?>
              <ul class="list-group mb-3">
                <?php foreach ($stages as $stage => $details) {
                  if ($details->required) { ?>
                    <?php $showButton = ($onboardingMember->isCurrentTask($stage) && !$displayed); ?>
                    <li class="list-group-item <?php if ($showButton) { ?>py-3 fw-bold<?php } else { ?>disabled<?php } ?>">
                      <div class="d-flex justify-content-between align-items-center">
                        <span><?= htmlspecialchars($tasks[$stage]) ?></span><?php if ($details->completed) { ?><span class="badge bg-success rounded-pill"><i class="fa fa-check-circle" aria-hidden="true"></i> Done</span><?php } else { ?><span class="badge bg-warning text-dark rounded-pill"><i class="fa fa-minus-circle" aria-hidden="true"></i> Pending</span><?php } ?>
                      </div>
                      <?php if ($showButton) { ?>
                        <p class="mb-0 mt-2">
                          <a href="<?= htmlspecialchars(autoUrl('onboarding/go/member-forms/' . $onboardingMember->id . '/start-task')) ?>" class="btn btn-success">Complete task <i class="fa fa-chevron-circle-right" aria-hidden="true"></i></a>
                        </p>
                    <?php }
                    } ?>
                    </li>
                    <?php if ($showButton) $displayed = true; ?>
                  <?php } ?>
              </ul>
            <?php } else { ?>
              <div class="alert alert-info">
                <p class="mb-0">
                  <strong>There are no tasks to complete for <?= htmlspecialchars($member->firstName . ' ' . $member->lastName) ?></strong>
                </p>
              </div>
            <?php } ?>
          <?php } while ($member = $getMembers->fetch(PDO::FETCH_OBJ)); ?>
        <?php } ?>

        <?php if ($session->checkMemberTasksComplete()) {
          $session->completeTask('member_forms');
          ?>
          <div class="alert alert-info">
            <p class="mb-0">
              <strong>There are no member tasks to complete</strong>
            </p>
            <p class="mb-0">
              We have marked the member section as complete. <a href="<?= htmlspecialchars(autoUrl('onboarding/go')) ?>" class="alert-link">Return to main page</a>.
            </p>
          </div>
        <?php } ?>

        </form>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();

?>