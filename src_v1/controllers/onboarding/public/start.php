<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$pagetitle = 'Welcome - Onboarding';

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
        <?php if (isset($_SESSION['SetupMandateSuccess'])) { ?>
          <div class="alert alert-success">
            <p class="mb-0">
              <strong>We've started setting up your direct debit instruction</strong>
            </p>
            <p class="mb-0">
              Account <?= htmlspecialchars($_SESSION['SetupMandateSuccess']['SortCode']) ?>, <?= htmlspecialchars($_SESSION['SetupMandateSuccess']['Last4']) ?>
            </p>
          </div>
        <?php unset($_SESSION['SetupMandateSuccess']);
        } ?>

        <?php if (isset($_SESSION['PaymentSuccess'])) { ?>
          <div class="alert alert-success">
            <p class="mb-0">
              <strong>Your payment has been successful</strong>
            </p>
          </div>
        <?php unset($_SESSION['PaymentSuccess']);
        } ?>

        <?php if ($session->renewal) { ?>
          <h1 class="text-center">Hi <?= htmlspecialchars($user->getFirstName()) ?>! Welcome to Membership Renewal</h1>
        <?php } else { ?>
          <h1 class="text-center">Hi <?= htmlspecialchars($user->getFirstName()) ?>! Welcome to <?= htmlspecialchars($tenant->getName()) ?></h1>
        <?php } ?>

        <h2 class="text-center">We now need you to</h2>

        <ul class="list-group my-5">
          <?php foreach ($stages as $stage => $details) {
            if ($details->required) { ?>
              <li class="list-group-item <?php if ($session->isCurrentTask($stage)) { ?>py-3 fw-bold<?php } else { ?>disabled<?php } ?>">
                <div class="d-flex justify-content-between align-items-center">
                  <span><?= htmlspecialchars($tasks[$stage]) ?></span><?php if ($details->completed) { ?><span class="badge bg-success rounded-pill"><i class="fa fa-check-circle" aria-hidden="true"></i> Done</span><?php } else { ?><span class="badge bg-warning text-dark rounded-pill"><i class="fa fa-minus-circle" aria-hidden="true"></i> Pending</span><?php } ?>
                </div>
                <?php if ($session->isCurrentTask($stage)) { ?>
                  <p class="mb-0 mt-2">
                    <a href="<?= htmlspecialchars(autoUrl('onboarding/go/start-task')) ?>" class="btn btn-success">Complete task <i class="fa fa-chevron-circle-right" aria-hidden="true"></i></a>
                  </p>
              <?php }
              } ?>
              </li>
            <?php } ?>
        </ul>

        <?php if ($session->isCurrentTask('done')) { ?>
          <p>
            You're all set. Welcome to <?= htmlspecialchars(app()->tenant->getName()) ?>.
          </p>

          <?php if (!isset(app()->user)) { ?>
            <p>
              <a href="<?= htmlspecialchars(autoUrl('onboarding/go/sign-in')) ?>" class="btn btn-success">Continue to your account</a>
            </p>
          <?php } else { ?>
            <p>
              <a href="<?= htmlspecialchars(autoUrl('')) ?>" class="btn btn-success">Return to your account</a>
            </p>
          <?php } ?>
        <?php } else { ?>

          <?php if ($session->dueDate) { ?>
            <p>
              You must complete all registration tasks by the end of <?= htmlspecialchars($session->dueDate->format('j F Y')) ?>.
            </p>
          <?php } ?>

          <p>
            The <?= htmlspecialchars($tenant->getName()) ?> team.
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