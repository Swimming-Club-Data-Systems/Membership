<?php

$db = app()->db;

$venue_details = [
  'name' => '',
  'address' => '',
];
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NewVenueError']['Data'])) {
  $venue_details = $_SESSION['TENANT-' . app()->tenant->getId()]['NewVenueError']['Data'];
}

$pagetitle = "Add a venue";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance')) ?>">Attendance</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('attendance/venues')) ?>">Venues</a></li>
        <li class="breadcrumb-item active" aria-current="page">New</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg">
        <h1>
          Add a venue
        </h1>
        <p class="lead mb-0">
          Venues used for sessions at <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>
        </p>
        <!-- <div class="mb-3 d-lg-none"></div> -->
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NewVenueError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['NewVenueError']['Status']) { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>
              Some required information was missing
            </strong>
          </p>
          <p class="mb-0">
            Please check the data you supplied and try again.
          </p>
        </div>
      <?php } ?>

      <form method="post" class="needs-validation" novalidate>
        <div class="mb-3">
          <label class="form-label" for="name">Venue Name</label>
          <input type="text" class="form-control" name="name" id="name" aria-describedby="nameHelp" placeholder="Enter name" value="<?=$venue_details['name']?>" required>
          <div class="invalid-feedback">
            You must enter a venue name
          </div>
          <small id="nameHelp" class="form-text text-muted">Enter the venue name, not the building. For example, this might be the "Small Pool, Anytown Leisure Centre" or the "Main Pool, Anytown Leisure Centre".</small>
        </div>

        <div class="mb-3">
          <label class="form-label" for="address">Address</label>
          <input type="text" class="form-control" name="address" id="address" aria-describedby="addressHelp" placeholder="Enter address" value="<?=$venue_details['address']?>" required>
          <div class="invalid-feedback">
            You must enter an address
          </div>
          <small id="addressHelp" class="form-text text-muted">Enter the address for the venue with each line separated by commas. For example, "Anytown Leisure Centre, Main Road, Anytown, AN1 1TON"</small>
        </div>

        <p>
          <button class="btn btn-primary" type="submit">
            Add venue
          </button>
        </p>
      </form>
    </div>
  </div>
</div>

<?php

unset($_SESSION['TENANT-' . app()->tenant->getId()]['NewVenueError']);

$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->render();
