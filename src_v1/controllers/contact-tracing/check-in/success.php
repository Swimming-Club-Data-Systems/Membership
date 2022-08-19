<?php

use function GuzzleHttp\json_decode;

$db = app()->db;
$tenant = app()->tenant;

$getLocation = $db->prepare("SELECT `ID`, `Name`, `Address` FROM covidLocations WHERE `ID` = ? AND `Tenant` = ?");
$getLocation->execute([
  $id,
  $tenant->getId()
]);
$location = $getLocation->fetch(PDO::FETCH_ASSOC);

if (!$location) {
  halt(404);
}

$pagetitle = 'Check In Success - Contact Tracing';

$locationAddress = json_decode($location['Address']);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing')) ?>">Tracing</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing/locations')) ?>">Locations</a></li>
        <li class="breadcrumb-item active" aria-current="page">Check In</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Checked in to <?= htmlspecialchars($location['Name']) ?>
        </h1>
        <p class="lead mb-0">
          <?= htmlspecialchars($locationAddress->streetAndNumber) ?>
        </p>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <p class="mb-0">
        <strong>Checked in successfully</strong>
      </p>
      <p>
        Thank you for checking in to <?= htmlspecialchars($location['Name']) ?>. This will help us support NHS Test and Trace if required.
      </p>

      <p>
        <a href="<?= htmlspecialchars(autoUrl('contact-tracing')) ?>" class="btn btn-success">Contact tracing home</a>
      </p>

    </div>
  </div>

</div>

<?php

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingSuccess'])) {
  unset($_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingSuccess']);
}

$footer = new \SCDS\Footer();
$footer->render();
