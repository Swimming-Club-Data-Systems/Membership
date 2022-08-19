<?php

$user = app()->user;
if (!$user->hasPermissions(['Admin'])) halt(404);

$pagetitle = 'New Qualification Type';

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">

  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('qualifications')) ?>">Qualifications</a></li>
        <li class="breadcrumb-item active" aria-current="page">New</li>
      </ol>
    </nav>

    <h1>Add a new qualification type</h1>
    <p class="lead mb-0">
      Track member qualifications
    </p>
  </div>

</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['FormError'])) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>Error</strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['FormError']) ?>
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['FormError']);
      } ?>

      <form method="post" class="needs-validation" novalidate>

        <div class="mb-3">
          <label class="form-label" for="qualification-name">Qualification name</label>
          <input type="text" name="qualification-name" id="qualification-name" class="form-control" required>
          <div class="invalid-feedback">
            Please enter a name for this qualification
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="qualification-description">Qualification description (optional)</label>
          <textarea name="qualification-description" id="qualification-description" class="form-control"></textarea>
          <div class="invalid-feedback">
            Please enter a name for this qualification
          </div>
        </div>

        <div class="mb-3" id="expires-box">
          <div class="form-check">
            <input type="radio" id="expires-no" name="expires" class="form-check-input" value="no" required checked>
            <label class="form-check-label" for="expires-no">This qualification never expires</label>
          </div>
          <div class="form-check">
            <input type="radio" id="expires-yes" name="expires" class="form-check-input" value="yes">
            <label class="form-check-label" for="expires-yes">This qualification expires</label>
          </div>
        </div>

        <div id="expires-options" class="d-none">
          <p>
            After how long does this qualification usually expire? Select a unit and then enter a number.
          </p>
          <div class="mb-3" id="expires-when-type">
            <div class="form-check">
              <input type="radio" id="expires-years" name="expires-when-type" class="form-check-input requirable" value="years" checked>
              <label class="form-check-label" for="expires-years">Years</label>
            </div>
            <div class="form-check">
              <input type="radio" id="expires-months" name="expires-when-type" class="form-check-input" value="months">
              <label class="form-check-label" for="expires-months">Months</label>
            </div>
            <div class="form-check">
              <input type="radio" id="expires-days" name="expires-when-type" class="form-check-input" value="days">
              <label class="form-check-label" for="expires-days">Days</label>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="expires-when">Expires after</label>
            <div class="input-group">
              <input type="number" placeholder="0" name="expires-when" id="expires-when" class="form-control requirable" min="1" step="1">
              <span class="input-group-text rounded-end" id="expires-when-addon">years</span>
              <div class="invalid-feedback">
                Please enter the normal expected lifetime of this qualification
              </div>
            </div>

          </div>
        </div>

        <?= \SCDS\CSRF::write(); ?>

        <p>
          <button type="submit" class="btn btn-success">
            Add
          </button>
        </p>
      </form>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->addJs('public/js/qualifications/add-edit.js');
$footer->render();
