<?php

$db = app()->db;
$tenant = app()->tenant;
$pagetitle = 'Contact Tracing';

// Show if this user is a squad rep
$getRepCount = $db->prepare("SELECT COUNT(*) FROM squadReps WHERE User = ?");
$getRepCount->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
]);
$showSignOut = $getRepCount->fetchColumn() > 0;

$user = app()->user;
if ($user->hasPermission('Admin') || $user->hasPermission('Coach') || $user->hasPermission('Galas')) {
  $showSignOut = true;
}

if (!$showSignOut) {
  // Redirect to location picker
  http_response_code(302);
  header("location: " . autoUrl('covid/contact-tracing/check-in'));
} else {

  include BASE_PATH . 'views/header.php';

?>

  <div class="bg-light mt-n3 py-3 mb-3">
    <div class="container-xl">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item active" aria-current="page">Tracing</li>
        </ol>
      </nav>

      <div class="row align-items-center">
        <div class="col-lg-8">
          <h1>
            Contact Tracing
          </h1>
          <p class="lead mb-0">
            <?= htmlspecialchars($tenant->getName()) ?> <em>Supporting NHS Test and Trace</em>
          </p>
        </div>
      </div>
    </div>
  </div>

  <div class="container-xl">

    <div class="row">
      <div class="col-md-6 mb-3">
        <div class="card card-body bg-danger text-white h-100" style="display: grid;">
          <div>
            <h2>
              Register your attendance
            </h2>
            <p class="lead">
              For contact tracing purposes.
            </p>
            <p>
              <?= htmlspecialchars($tenant->getName()) ?> will use this data if required to support NHS Test and Trace. Data will be deleted automatically after 21 days.
            </p>
          </div>
          <p class="mb-0 mt-auto d-flex">
            <a href="<?= htmlspecialchars(autoUrl('contact-tracing/check-in')) ?>" class="btn btn-outline-light btn-outline-light-d">
              Register <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>
            </a>
          </p>
        </div>
      </div>
      <?php if ($showSignOut) { ?>
        <div class="col-md-6 mb-3">
          <div class="card card-body bg-success text-white h-100" style="display: grid;">
            <div>
              <h2>
                Sign Out
              </h2>
              <p class="lead">
                Sign people out of a location.
              </p>
              <p>
                Useful for safely releasing children from a building.
              </p>
            </div>
            <p class="mb-0 mt-auto d-flex">
              <a href="<?= htmlspecialchars(autoUrl('contact-tracing/sign-out')) ?>" class="btn btn-outline-light btn-outline-light-d">
                Sign Out <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>
              </a>
            </p>
          </div>
        </div>
      <?php } ?>
      <?php if (app()->user->hasPermission('Admin')) { ?>
        <div class="col-md mb-3">
          <div class="card card-body h-100" style="display: grid;">
            <div>
              <h2>
                Manage locations
              </h2>
            </div>
            <p class="mb-0 mt-auto d-flex">
              <a href="<?= htmlspecialchars(autoUrl('contact-tracing/locations')) ?>" class="btn btn-dark btn-light-d">
                Manage
              </a>
            </p>
          </div>
        </div>

        <div class="col-md mb-3">
          <div class="card card-body h-100" style="display: grid;">
            <div>
              <h2>
                Generate reports
              </h2>
            </div>
            <p class="mb-0 mt-auto d-flex">
              <a href="<?= htmlspecialchars(autoUrl('contact-tracing/reports')) ?>" class="btn btn-dark btn-light-d">
                Reports
              </a>
            </p>
          </div>
        </div>
      <?php } ?>
    </div>

  </div>

<?php

  $footer = new \SCDS\Footer();
  $footer->render();
}
