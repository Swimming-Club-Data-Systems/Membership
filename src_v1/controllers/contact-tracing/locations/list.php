<?php

$db = app()->db;
$tenant = app()->tenant;
$pagetitle = 'Locations - Contact Tracing';

$getLocations = $db->prepare("SELECT `ID`, `Name` FROM `covidLocations` WHERE `Tenant` = ? ORDER BY `Name` ASC");
$getLocations->execute([
  $tenant->getId(),
]);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing')) ?>">Tracing</a></li>
        <li class="breadcrumb-item active" aria-current="page">Locations</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Contact Tracing Locations
        </h1>
        <p class="lead mb-0">
          Locations used for contact tracing
        </p>
        <div class="mb-3 d-lg-none"></div>
      </div>
      <?php if (app()->user->hasPermission('Admin')) { ?>
      <div class="col text-end">
        <div class="btn-group" role="group" aria-label="Quick options">
          <a href="<?=htmlspecialchars(autoUrl("contact-tracing/locations/new"))?>" class="btn btn-success">New</a>
        </div>
      </div>
      <?php } ?>
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <?php if ($location = $getLocations->fetch(PDO::FETCH_ASSOC)) { ?>
        <div class="list-group">
          <?php do { ?>
            <a href="<?= htmlspecialchars(autoUrl('contact-tracing/locations/' . $location['ID'])) ?>" class="list-group-item list-group-item-action">
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
            <a href="<?= htmlspecialchars(autoUrl('contact-tracing/locations/new')) ?>" class="alert-link">Add one</a> to get started.
          </p>
        </div>
      <?php } ?>
    </div>
  </div>
  
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();