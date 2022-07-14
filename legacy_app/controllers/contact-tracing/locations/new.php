<?php

$db = app()->db;
$tenant = app()->tenant;
$pagetitle = 'New Location - Contact Tracing';

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing')) ?>">Tracing</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing/locations')) ?>">Locations</a></li>
        <li class="breadcrumb-item active" aria-current="page">New</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          New Location
        </h1>
        <p class="lead mb-0">
          Add locations such as leisure centres
        </p>
        <div class="mb-3 d-lg-none"></div>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <form method="post" class="needs-validation" novalidate>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NewLocationError'])) { ?>
          <div class="alert alert-danger">
            <p class="mb-0">
              <strong>An error occurred</strong>
            </p>
            <p class="mb-0">
              <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['NewLocationError']) ?>
            </p>
          </div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['NewLocationError']);
        } ?>

        <div class="mb-3">
          <label class="form-label" for="location-name">Name</label>
          <input type="text" class="form-control" name="location-name" id="location-name" required>
          <div class="invalid-feedback">
            Please provide a name for this location.
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="street-and-number">Address line 1 (street and number)</label>
          <input class="form-control" name="street-and-number" id="street-and-number" type="text" autocomplete="address-line1" required>
          <div class="invalid-feedback">
            Please enter the street and property name or number.
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="flat-building">Address line 2 (optional)</label>
          <input class="form-control" name="flat-building" id="flat-building" type="text" autocomplete="address-line2">
        </div>

        <div class="mb-3">
          <label class="form-label" for="town-city">Town/City</label>
          <input class="form-control" name="town-city" id="town-city" type="text" autocomplete="address-level2" required>
          <div class="invalid-feedback">
            Please enter the town or city.
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="post-code">Post Code</label>
          <input class="form-control" name="post-code" id="post-code" type="text" autocomplete="postal-code" required pattern="[A-Za-z]{1,2}[0-9Rr][0-9A-Za-z]?[\s]{0,1}[0-9][ABD-HJLNP-UW-Zabd-hjlnp-uw-z]{2}">
          <div class="invalid-feedback">
            Please enter a valid post code.
          </div>
        </div>

        <?= SCDS\CSRF::write() ?>

        <p>
          <button type="submit" class="btn btn-success">
            Add location
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
