<?php

$db = app()->db;
$tenant = app()->tenant;

$getRecentGalas = $db->prepare("SELECT `GalaName`, `GalaID` FROM `galas` WHERE Tenant = ? AND `GalaDate` >= ? AND `GalaDate` <= ?");
$date = new DateTime('now', new DateTimeZone('Europe/London'));
$today = $date->format("Y-m-d");
$threeWeeksAgo = ($date->sub(new DateInterval('P21D')))->format("Y-m-d");
$getRecentGalas->execute([
  $tenant->getId(),
  $threeWeeksAgo,
  $today
]);

$pagetitle = "SDIF Upload";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin")) ?>">Admin</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin/galas")) ?>">Galas</a></li>
      <!-- <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin/galas/sdif")) ?>">Admin</a></li> -->
      <li class="breadcrumb-item active" aria-current="page">Upload</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>SDIF Upload</h1>
      <p class="lead">Upload times for your members quickly using an SDIF (*.sd3) file.</p>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UploadSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['UploadSuccess']) { ?>
        <div class="alert alert-success">
          <p class="mb-0"><strong>Results have been uploaded</strong>.</p>
        </div>
      <?php
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['UploadSuccess']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['FormError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['FormError']) { ?>
        <div class="alert alert-danger">
          <p class="mb-0"><strong>We could not verify the integrity of the submitted form</strong>. Please try again.</p>
        </div>
      <?php
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['FormError']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UploadError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['UploadError']) { ?>
        <div class="alert alert-danger">
          <p class="mb-0"><strong>There was a problem with the file uploaded</strong>. Please try again.</p>
        </div>
      <?php
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['UploadError']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['TooLargeError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['TooLargeError']) { ?>
        <div class="alert alert-danger">
          <p class="mb-0"><strong>The file you uploaded was too large</strong>. The maximum file size is 300000 bytes.</p>
        </div>
      <?php
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['TooLargeError']);
      } ?>

      <p>This software will carry out checks to ensure it does not add duplicate records.</p>

      <form enctype="multipart/form-data" method="post">

        <?= \SCDS\FormIdempotency::write() ?>
        <?= \SCDS\CSRF::write() ?>
        <input type="hidden" name="MAX_FILE_SIZE" value="3000000">

        <div class="mb-3">
          <label class="form-label text-truncate" for="file-upload">Select a result file to upload</label>
          <input type="file" class="form-control" id="file-upload" name="file-upload[]" accept="text/plain,.sd3" multiple required>
        </div>

        <div class="mb-3">
          <label class="form-label" for="gala">Select a gala to link this file to</label>
          <select class="form-select" id="gala" name="gala" aria-describedby="galaHelp">
            <option value="0" selected>Don't link to an existing gala</option>
            <?php while ($gala = $getRecentGalas->fetch(PDO::FETCH_ASSOC)) { ?>
              <option value="<?= htmlspecialchars($gala['GalaID']) ?>">
                <?= htmlspecialchars($gala['GalaName']) ?>
              </option>
            <?php } ?>
          </select>
          <small id="galaHelp" class="form-text text-muted">If the gala finished in the last three weeks, you can link this uploaded file to the gala in the system which users used to make entries.</small>
        </div>

        <p>
          <button type="submit" class="btn btn-success">
            Upload
          </button>
        </p>

      </form>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/bs-custom-file-input.min.js");
$footer->addJS("js/file-input-init.js");
$footer->render();
