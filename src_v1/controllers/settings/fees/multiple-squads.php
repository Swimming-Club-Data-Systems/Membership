<?php

$db = app()->db;

$fluidContainer = true;

$pagetitle = "Multiple Squad Fees";

$option = app()->tenant->getKey('FeesWithMultipleSquads');

if (!$option) {
  $option = 'Full';
}

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
        <li class="breadcrumb-item active" aria-current="page">Multiple Squads</li>
      </ol>
    </nav>

      <main>
        <h1>Multiple Squad Fees</h1>
        <p class="lead">Fee settings for members in multiple squads</p>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success']) && $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success']) { ?>
        <div class="alert alert-success">Changes saved successfully</div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success']); } ?>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']) && $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']) { ?>
        <div class="alert alert-danger">Changes could not be saved</div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']); } ?>

        <form method="post">

          <p>When a new member is assigned to multiple squads, what should we charge them?.</p>
          <div class="mb-3">
            <label class="form-label" for="upgrade">Options</label>
            <div class="form-check">
              <input type="radio" id="full-fee" value="Full" name="fee-option" class="form-check-input" <?php if ($option == 'Full') { ?>checked<?php } ?>>
              <label class="form-check-label" for="full-fee">Charge the full fee for all squads</label>
            </div>
            <div class="form-check">
              <input type="radio" id="max-fee" value="MaxFee" name="fee-option" class="form-check-input" <?php if ($option == 'MaxFee') { ?>checked<?php } ?> disabled>
              <label class="form-check-label" for="max-fee">Charge the maximum fee (from the member's squads)</label>
            </div>
          </div>

          <p>
            <button class="btn btn-success" type="submit">
              Save
            </button>
          </p>
        </form>
      </main>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();