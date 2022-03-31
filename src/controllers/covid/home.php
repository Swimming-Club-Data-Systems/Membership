<?php

$db = app()->db;
$tenant = app()->tenant;
$pagetitle = 'COVID Tools';

// Show if this user is a squad rep
$getRepCount = $db->prepare("SELECT COUNT(*) FROM squadReps WHERE User = ?");
$getRepCount->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
]);
$showSignOut = $getRepCount->fetchColumn() > 0;

$user = app()->user;

$showCovid = true;
if ($showCovid && $tenant->getBooleanKey('HIDE_CONTACT_TRACING_FROM_PARENTS')) {
  // Hide covid banners
  $showCovid = false;

  // Show if this user is a squad rep
  $getRepCount = $db->prepare("SELECT COUNT(*) FROM squadReps WHERE User = ?");
  $getRepCount->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
  ]);
  $showCovid = $getRepCount->fetchColumn() > 0;
}

if ($user->hasPermission('Admin') || $user->hasPermission('Coach') || $user->hasPermission('Galas')) {
  $showSignOut = true;
  $showCovid = true;
}

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">COVID</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          COVID-19 Tools
        </h1>
        <p class="lead mb-0">
          A number of COVID-19 tools have been provided to help clubs during COVID-19.
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="alert alert-info">
    <p class="mb-0">
      <strong>SCDS has now de-prioritised COVID-19 related features</strong>
    </p>
    <p>
      This follows the end of general COVID-19 measures and testing by the UK Government. As part of this we've started to move COVID links to the end of menus and will prepare to remove COVID features altogether.
    </p>
    <p class="mb-0">
      When we remove these features, we will also remove COVID-19 related data from our database.
    </p>
  </div>

  <div class="row mb-0">

    <?php if ($showCovid) { ?>
      <div class="col-md-6 pb-3">
        <div class="card card-body h-100" style="display: grid;">
          <div>
            <h2>
              Contact Tracing
            </h2>
            <p class="lead">
              We're keeping a record of those attending sessions.
            </p>
            <p>
              <?= htmlspecialchars($tenant->getName()) ?> can use your contact data (if required) to support NHS Test and Trace.
            </p>
          </div>
          <p class="mb-0 mt-auto d-flex">
            <a href="<?= htmlspecialchars(autoUrl('covid/contact-tracing')) ?>" class="btn btn-primary">
              Go
            </a>
          </p>
        </div>
      </div>
    <?php } ?>

    <div class="col-md-6 pb-3">
      <div class="card card-body h-100" style="display: grid;">
        <div>
          <h2>
            Health Screening Survey
          </h2>
          <p class="lead">
            Swim England are recommending that all clubs carry out a periodic screening survey of all members who are training.
          </p>
          <p>
            Taking the screening survey helps keep yourself and other club members safe.
          </p>
        </div>
        <p class="mb-0 mt-auto d-flex">
          <a href="<?= htmlspecialchars(autoUrl('covid/health-screening')) ?>" class="btn btn-primary">
            Go
          </a>
        </p>
      </div>
    </div>

    <div class="col-md-6 pb-3">
      <div class="card card-body h-100" style="display: grid;">
        <div>
          <h2>
            <?php if (mb_strtoupper(app()->tenant->getKey('ASA_CLUB_CODE')) == 'UOSZ') { ?><?= htmlspecialchars(UOS_RETURN_FORM_NAME) ?><?php } else { ?>Risk Awareness Declaration<?php } ?>
          </h2>
          <p class="lead">
            Declare that you understand the risks of returning to training.
          </p>
          <p>
            You also confirm you are free from any COVID-19 symptoms.
          </p>
        </div>
        <p class="mb-0 mt-auto d-flex">
          <a href="<?= htmlspecialchars(autoUrl('covid/risk-awareness')) ?>" class="btn btn-primary">
            Go
          </a>
        </p>
      </div>
    </div>

    <div class="col-md-6 pb-3">
      <div class="card card-body h-100" style="display: grid;">
        <div>
          <h2>
            Return to Competition
          </h2>
          <p class="lead">
            Making sure you're safe to compete.
          </p>
          <p>
            Some gala hosts require that that all clubs carry out a screening survey of all members who are competing at a given gala.
          </p>
        </div>
        <p class="mb-0 mt-auto d-flex">
          <a href="<?= htmlspecialchars(autoUrl('covid/competition-health-screening')) ?>" class="btn btn-primary">
            Go
          </a>
        </p>
      </div>
    </div>

  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
