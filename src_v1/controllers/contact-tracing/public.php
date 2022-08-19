<?php

$db = app()->db;
$tenant = app()->tenant;
$pagetitle = 'Contact Tracing';

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
        <li class="breadcrumb-item active" aria-current="page">Tracing</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Contact Tracing
        </h1>
        <p class="lead mb-0">
          Select your current location to check in
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <?php if ($location = $getLocations->fetch(PDO::FETCH_ASSOC)) { ?>
    <div class="card mb-5">
      <div class="card-header">
        Select
      </div>
      <div class="list-group list-group-flush">
        <?php do { ?>
          <a href="<?= htmlspecialchars(autoUrl('contact-tracing/check-in/' . $location['ID'])) ?>" class="list-group-item list-group-item-action">
            <?= htmlspecialchars($location['Name']) ?>
          </a>
        <?php } while ($location = $getLocations->fetch(PDO::FETCH_ASSOC)); ?>
      </div>
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

  <div class="row">

    <div class="col-md-4 mb-3">
      <div class="card card-body h-100 text-white" style="background-color: #005eb8 !important; display: grid;">
        <div>
          <h2>
            Coronavirus (COVID-19)
          </h2>
          <p class="lead">
            Find out about coronavirus, its symptoms and effects at NHS.UK.
          </p>
        </div>
        <p class="mb-0 mt-auto d-flex">
          <a href="https://www.nhs.uk/conditions/coronavirus-covid-19/" class="btn btn-light btn-light-d" target="_blanks">
            <i class="fa fa-arrow-circle-right" aria-hidden="true"></i> NHS.UK
          </a>
        </p>
      </div>
    </div>

    <div class="col-md-4 mb-3">
      <div class="card card-body h-100" style="display: grid;">
        <div>
          <h2>
            The Rules
          </h2>
          <p class="lead">
            Find out the rules and guidance on what you can and can't do on GOV.UK.
          </p>
        </div>
        <p class="mb-0 mt-auto d-flex">
          <a href="https://www.gov.uk/coronavirus" class="btn btn-dark btn-light-d" target="_blanks">
            <i class="fa fa-arrow-circle-right" aria-hidden="true"></i> GOV.UK
          </a>
        </p>
      </div>
    </div>

    <div class="col-md-4 mb-3">
      <div class="card card-body h-100" style="display: grid;">
        <div>
          <h2>
            Swim England Return to Pool Guidance
          </h2>
          <p class="lead">
            Swim England have issued guidance to all clubs and pool operators on returning to the pool safely. Read it on the Swim England website.
          </p>
        </div>
        <p class="mb-0 mt-auto d-flex">
          <a href="https://www.swimming.org/swimengland/pool-return-guidance-documents/" class="btn btn-dark btn-light-d" target="_blanks">
            <i class="fa fa-arrow-circle-right" aria-hidden="true"></i> Guidance
          </a>
        </p>
      </div>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
