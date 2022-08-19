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

$pagetitle = htmlspecialchars($location['Name']) . ' - Contact Tracing';

$address = json_decode($location['Address']);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing')) ?>">Tracing</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing/locations')) ?>">Locations</a></li>
        <!-- <li class="breadcrumb-item active" aria-current="page">Edit</li> -->
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          <?= htmlspecialchars($location['Name']) ?>
        </h1>
        <?php if (isset($address->streetAndNumber)) { ?>
        <p class="lead mb-0">
          <?= htmlspecialchars($address->streetAndNumber) ?>
        </p>
        <?php } ?>
        <div class="mb-3 d-lg-none"></div>
      </div>
      <?php if (app()->user->hasPermission('Admin')) {?>
      <div class="col text-end">
        <a href="<?= htmlspecialchars(autoUrl("contact-tracing/locations/$id/edit")) ?>" class="btn btn-success">
          Edit
        </a>
      </div>
      <?php } ?>
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <h3>Address</h3>
      <address>
        <?php if (isset($address->streetAndNumber) && $address->streetAndNumber) { ?>
          <?= htmlspecialchars($address->streetAndNumber) ?><br>
        <?php } ?>
        <?php if (isset($address->flatOrBuilding) && $address->flatOrBuilding) { ?>
          <?= htmlspecialchars($address->flatOrBuilding) ?><br>
        <?php } ?>
        <?php if (isset($address->city) && $address->city) { ?>
          <?= htmlspecialchars($address->city) ?><br>
        <?php } ?>
        <?php if (isset($address->postCode) && $address->postCode) { ?>
          <?= htmlspecialchars($address->postCode) ?>
        <?php } ?>
      </address>

      <h3>
        Poster
      </h3>

      <p>
        <a href="<?= htmlspecialchars(autoUrl("contact-tracing/locations/$id/poster")) ?>" class="btn btn-primary">
          Download location help poster
        </a>
      </p>

      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
        <!-- Admin functions for this location -->
      <?php } ?>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
