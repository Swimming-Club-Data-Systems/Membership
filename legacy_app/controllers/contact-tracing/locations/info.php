<?php

use function GuzzleHttp\json_decode;

$db = nezamy_app()->db;
$tenant = nezamy_app()->tenant;

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

$addr = json_decode($location['Address']);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container">

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
        <p class="lead mb-0">
          <?= htmlspecialchars($addr->streetAndNumber) ?>
        </p>
        <div class="mb-3 d-lg-none"></div>
      </div>
      <?php if (nezamy_app()->user->hasPermission('Admin')) {?>
      <div class="col text-right">
        <a href="<?= htmlspecialchars(autoUrl("contact-tracing/locations/$id/edit")) ?>" class="btn btn-success">
          Edit
        </a>
      </div>
      <?php } ?>
    </div>

  </div>
</div>

<div class="container">

  <div class="row">
    <div class="col-lg-8">
      <h3>Address</h3>
      <address>
        <?php if (isset($addr->streetAndNumber) && $addr->streetAndNumber) { ?>
          <?= htmlspecialchars($addr->streetAndNumber) ?><br>
        <?php } ?>
        <?php if (isset($addr->flatOrBuilding) && $addr->flatOrBuilding) { ?>
          <?= htmlspecialchars($addr->flatOrBuilding) ?><br>
        <?php } ?>
        <?php if (isset($addr->city) && $addr->city) { ?>
          <?= htmlspecialchars($addr->city) ?><br>
        <?php } ?>
        <?php if (isset($addr->postCode) && $addr->postCode) { ?>
          <?= htmlspecialchars($addr->postCode) ?>
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

      <?php if ($_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
        <!-- Admin functions for this location -->
      <?php } ?>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
