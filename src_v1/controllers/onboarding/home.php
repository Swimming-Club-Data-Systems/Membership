<?php

$user = app()->user;
$db = app()->db;

$pagetitle = "Onboarding";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Onboarding</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Welcome to onboarding
        </h1>
        <p class="lead mb-0">
          Onboarding is the replacement for assisted registration.
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <p>
        Onboarding works hand in hand with the new Membership Centre feature lets clubs track which memberships their members hold in a given year.
      </p>

      <?php if (app()->user->hasPermission('Admin')) { ?>
        <h2>
          Onboard a new user
        </h2>

        <p class="lead">
          Create and onboard a new user and associated members quickly and easily.
        </p>

        <p>
          <a href="<?= htmlspecialchars(autoUrl('onboarding/new')) ?>" class="btn btn-primary">Get started</a>
        </p>

        <h2>
          View all onboarding sessions
        </h2>

        <p class="lead">
          Find unfinished, pending or completed onboarding sessions.
        </p>

        <p>
          <a href="<?= htmlspecialchars(autoUrl('onboarding/all')) ?>" class="btn btn-primary">View all</a>
        </p>
      <?php } ?>

      <?php if (true) { ?>
        <h2>Complete onboarding</h2>

        <p class="lead">
          You have outstanding onboarding tasks to complete.
        </p>
      <?php } ?>

    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
