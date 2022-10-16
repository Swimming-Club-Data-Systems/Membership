<?php

$pagetitle = 'Member Uploads';

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin")) ?>">Admin</a></li>
      <li class="breadcrumb-item active" aria-current="page">Member upload</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>Member upload</h1>
      <p class="lead">Batch upload members from a <span class="font-monospace">CSV</span> file.</p>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UploadSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['UploadSuccess']) { ?>
        <div class="alert alert-success">
          <p class="mb-0"><strong>Members have been uploaded</strong>. Check below for any errors.</p>
        </div>
      <?php
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['UploadSuccess']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['FailedSwimmers'])) { ?>
        <div class="alert alert-warning">
          <p>We've uploaded most of your members but the following could not be uploaded as their squad could not be found;</p>
          <ul class="mb-0">
            <?php foreach ($_SESSION['TENANT-' . app()->tenant->getId()]['FailedSwimmers'] as $s) { ?>
              <li><?= htmlspecialchars($s) ?></li>
            <?php } ?>
          </ul>
        </div>
      <?php
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['FailedSwimmers']);
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
          <p class="mb-0"><strong>The file you uploaded was too large</strong>. The maximum file size is 30000 bytes.</p>
        </div>
      <?php
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['TooLargeError']);
      } ?>

      <p>Your CSV file should contain the following columns in the order shown without any column headers (titles etc);</p>

      <ul class="font-monospace">
        <li>Last name</li>
        <li>First name</li>
        <li>Squad name</li>
        <li>Date of birth (DD/MM/YYYY format)</li>
        <li>Sex (M or F)</li>
        <li>Swim England Membership Category (1, 2 or 3 - use 0 or null if not a Swim England member)</li>
        <li>Swim England Membership Number, null if not a member or no number available</li>
      </ul>

      <p>Note that the system will attempt to find a squad with the name given. If it cannot find one, it will still add the member, but not assign them to any squads. If members are in multiple squads, you must add them to additional squads later - The uploader will only put a member in a single squad.</p>

      <form enctype="multipart/form-data" method="post" class="needs-validation" novalidate>

        <?= \SCDS\FormIdempotency::write() ?>
        <?= \SCDS\CSRF::write() ?>
        <input type="hidden" name="MAX_FILE_SIZE" value="30000">

        <div class="mb-3">
          <label class="form-label" for="file-upload">Select a member file to upload</label>
          <input type="file" class="form-control" id="file-upload" name="file-upload" accept="text/csv" required>
          <div class="invalid-feedback">
            Please select a CSV file to upload
          </div>
        </div>

        <p>
          <button type="submit" class="btn btn-success">
            Upload
          </button>
        </p>

      </form>
    </div>

    <div class="col">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/admin-tools/list.json'));
      echo $list->render('admin-member-upload');
      ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/bs-custom-file-input.min.js");
$footer->addJS("js/file-input-init.js");
$footer->addJS("js/NeedsValidation.js");
$footer->render();
