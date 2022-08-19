<?php

$db = app()->db;

$fluidContainer = true;

if (!isset($_GET['type']) || !in_array($_GET['type'], ['club', 'national_governing_body', 'other'])) halt(404);

$type = 'Club Membership';
switch ($_GET['type']) {
  case 'national_governing_body':
    $type = htmlspecialchars(app()->tenant->getKey('NGB_NAME')) . ' Membership';
    break;
  case 'other':
    $type = 'Other (Arbitrary) Membership';
    break;
}

$pagetitle = "New Membership Category";

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">
  <div class="row justify-content-between">
    <aside class="col-md-3 d-none d-md-block">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/settings/SettingsLinkGroup.json'));
      echo $list->render('settings-fees');
      ?>
    </aside>
    <div class="col-md-9">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('settings')) ?>">Settings</a></li>
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('settings/fees')) ?>">Fees</a></li>
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('settings/fees/membership-fees')) ?>">Memberships</a></li>
          <li class="breadcrumb-item active" aria-current="page">New Class</li>
        </ol>
      </nav>

      <main>
        <h1>New <?= htmlspecialchars($type) ?> Fee Class</h1>
        <p class="lead">Set amounts for membership fees</p>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']) && $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']) { ?>
          <div class="alert alert-danger">Your new membership class could not be saved</div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']);
        } ?>

        <form method="post" class="needs-validation" novalidate>

          <div class="mb-3">
            <label class="form-label" for="membership-type-name">Membership Type</label>
            <input type="text" name="membership-type-name" id="membership-type-name" class="form-control" value="<?= htmlspecialchars($type) ?>" readonly>
          </div>

          <input type="hidden" name="membership-type" value="<?= htmlspecialchars($_GET['type']) ?>">

          <div class="mb-3">
            <label class="form-label" for="class-name">Class Name</label>
            <input type="text" name="class-name" id="class-name" class="form-control" required>
            <div class="invalid-feedback">
              Please provide a name for this type of membership
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="class-description">Description (optional)</label>
            <textarea class="form-control" name="class-description" id="class-description" rows="5"></textarea>
          </div>

          <p>
            We'll set the fees for this class on the next page.
          </p>

          <?= \SCDS\CSRF::write(); ?>

          <p>
            <button type="submit" class="btn btn-success">Add class</button>
          </p>

        </form>

      </main>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
