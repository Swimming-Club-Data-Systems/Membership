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

$pagetitle = 'Edit ' . htmlspecialchars($location['Name']) . ' - Contact Tracing';

$address = json_decode($location['Address']);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing')) ?>">Tracing</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing/locations')) ?>">Locations</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
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
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <form method="post" class="needs-validation" novalidate>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NewLocationSuccess'])) { ?>
          <div class="alert alert-success">
            <p class="mb-0">
              <strong>Success</strong>
            </p>
            <p class="mb-0">
              We've added your new location
            </p>
          </div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['NewLocationSuccess']);
        } ?>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateLocationSuccess'])) { ?>
          <div class="alert alert-success">
            <p class="mb-0">
              <strong>Success</strong>
            </p>
            <p class="mb-0">
              We've updated the details for this location
            </p>
          </div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateLocationSuccess']);
        } ?>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateLocationError'])) { ?>
          <div class="alert alert-danger">
            <p class="mb-0">
              <strong>Error</strong>
            </p>
            <p class="mb-0">
              <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateLocationError']) ?>
            </p>
          </div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateLocationError']);
        } ?>

        <div class="mb-3">
          <label class="form-label" for="location-name">Name</label>
          <input type="text" class="form-control" name="location-name" id="location-name" value="<?= htmlspecialchars($location['Name']) ?>" required>
          <div class="invalid-feedback">
            Please provide a name for this location.
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="street-and-number">Address line 1 (street and number)</label>
          <input class="form-control" name="street-and-number" id="street-and-number" type="text" autocomplete="address-line1" <?php if (isset($address->streetAndNumber) && $address->streetAndNumber) { ?> value="<?= htmlspecialchars($address->streetAndNumber) ?>" <?php } ?> required>
          <div class="invalid-feedback">
            Please enter the street and property name or number.
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="flat-building">Address line 2 (optional)</label>
          <input class="form-control" name="flat-building" id="flat-building" type="text" autocomplete="address-line2" <?php if (isset($address->flatOrBuilding) && $address->flatOrBuilding) { ?> value="<?= htmlspecialchars($address->flatOrBuilding) ?>" <?php } ?>>
        </div>

        <div class="mb-3">
          <label class="form-label" for="town-city">Town/City</label>
          <input class="form-control" name="town-city" id="town-city" type="text" autocomplete="address-level2" required <?php if (isset($address->city) && $address->city) { ?> value="<?= htmlspecialchars($address->city) ?>" <?php } ?>>
          <div class="invalid-feedback">
            Please enter the town or city.
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="post-code">Post Code</label>
          <input class="form-control" name="post-code" id="post-code" type="text" autocomplete="postal-code" required pattern="[A-Za-z]{1,2}[0-9Rr][0-9A-Za-z]?[\s]{0,1}[0-9][ABD-HJLNP-UW-Zabd-hjlnp-uw-z]{2}" <?php if (isset($address->postCode) && $address->postCode) { ?> value="<?= htmlspecialchars($address->postCode) ?>" <?php } ?>>
          <div class="invalid-feedback">
            Please enter a valid post code.
          </div>
        </div>

        <?= SCDS\CSRF::write() ?>

        <p>
          <button type="submit" class="btn btn-success">
            Save
          </button>
        </p>

      </form>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
