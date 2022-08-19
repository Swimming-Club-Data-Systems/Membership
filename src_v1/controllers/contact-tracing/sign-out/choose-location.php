<?php

$db = app()->db;
$tenant = app()->tenant;
$pagetitle = 'Sign Out - Contact Tracing';

$getLocations = $db->prepare("SELECT `ID`, `Name` FROM `covidLocations` WHERE `Tenant` = ? ORDER BY `Name` ASC");
$getLocations->execute([
  $tenant->getId(),
]);

// Check if authenticated
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
  halt(404);
}

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing')) ?>">Tracing</a></li>
        <li class="breadcrumb-item active" aria-current="page">Sign Out</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Sign Out
        </h1>
        <p class="lead mb-0">
          Sign out those present at a location
        </p>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <?php if ($location = $getLocations->fetch(PDO::FETCH_ASSOC)) { ?>
        <div class="list-group">
          <?php do { ?>
            <a href="<?= htmlspecialchars(autoUrl('contact-tracing/sign-out/' . $location['ID'])) ?>" class="list-group-item list-group-item-action">
              <?= htmlspecialchars($location['Name']) ?>
            </a>
          <?php } while ($location = $getLocations->fetch(PDO::FETCH_ASSOC)); ?>
        </div>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>No locations yet!</strong>
          </p>
          <p class="mb-0">
            Club staff can add a location so that you can get started.
          </p>
        </div>
      <?php } ?>
    </div>
  </div>
  
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();