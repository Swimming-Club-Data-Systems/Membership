<?php

$fluidContainer = true;

$db = app()->db;
$tenant = app()->tenant;

$pagetitle = "Club Logo";

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">
  <div class="row justify-content-between">
    <aside class="col-md-3 d-none d-md-block">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/settings/SettingsLinkGroup.json'));
      echo $list->render('settings-logo');
      ?>
    </aside>
    <main class="col-md-9">
      <h1>Upload club logo</h1>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LOGO-SAVED']) && $_SESSION['TENANT-' . app()->tenant->getId()]['LOGO-SAVED']) { ?>
        <div class="alert alert-success">New logo saved.</div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['LOGO-SAVED']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LOGO-ERROR']) && $_SESSION['TENANT-' . app()->tenant->getId()]['LOGO-ERROR']) { ?>
        <div class="alert alert-danger">Your new logo could not be saved.</div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['LOGO-ERROR']);
      } ?>

      <?php if ($logos = $tenant->getKey('LOGO_DIR')) { ?>
        <p>You currently have the below image set as your logo:</p>
        <div class="card card-body mb-3">
          <img src="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-150.png')) ?>" srcset="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-150@2x.png')) ?> 2x, <?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-150@3x.png')) ?> 3x" alt="<?= htmlspecialchars($tenant->getName()) ?> logo" class="img-fluid mx-auto">
        </div>
      <?php } ?>

      <form class="needs-validation" method="post" id="logo-form" enctype="multipart/form-data" novalidate>

        <input type="hidden" name="MAX_FILE_SIZE" value="3145728">

        <p>
          We ask you to provide two versions of your logo.
        </p>

        <ul>
          <li>
            The first is your logo proper and we'll use this in emails and other parts of the membership system. Your logo can be any aspect ratio, but logos with an extreme width or height relative to the other may become distorted.
          </li>
          <li>
            The second is an icon. For best results, this should be a square icon in a 1:1 aspect ratio. We'll use this as your icon in browser tabs and on device home screens.
          </li>
        </ul>

        <p>
          Providing an icon is optional. If you don't, we will generate an icon from your logo.
        </p>

        <p>
          While we accept PNG and JPEG file uploads, we recommend you upload your logos an icons as PNG files as these are much better quality than JPEG files, which become noisy around text.
        </p>

        <div class="mb-3">
          <label class="form-label text-truncate" for="file-upload">Upload logo</label>
          <input type="file" class="form-control" accept="image/png,image/jpeg" id="file-upload" name="file-upload" data-max-total-file-size="10485760" data-max-file-size="3145728" data-error-message-id="file-upload-invalid-feedback" required>
          <div class="invalid-feedback" id="file-upload-invalid-feedback">
            Please include an image
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label text-truncate" for="icon-upload">Upload icon <small class="text-muted">Optional</small></label>
          <input type="file" class="form-control" accept="image/png,image/jpeg" id="icon-upload" name="icon-upload" data-max-total-file-size="10485760" data-max-file-size="3145728" data-error-message-id="icon-upload-invalid-feedback">
          <div class="invalid-feedback" id="icon-upload-invalid-feedback">
            Oh no!
          </div>
        </div>

        <p>
          We will convert your logo into multiple formats and store a master copy so that we can create versions of the logo at different sizes and file formats at a later date.
        </p>

        <p>
          It may take a few seconds for us to process your logo. Please wait patiently.
        </p>

        <p>
          <button class="btn btn-success" type="submit">
            Upload
          </button>
        </p>
      </form>
    </main>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->addJS("js/NeedsValidation.js");
$footer->addJS("js/settings/logo-upload.js");
$footer->render();
