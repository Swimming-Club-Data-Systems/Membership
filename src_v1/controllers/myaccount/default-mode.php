<?php

$fluidContainer = true;

$db = app()->db;
$currentUser = app()->user;

$perms = $currentUser->getPrintPermissions();
$default = $currentUser->getUserOption('DefaultAccessLevel');

$pagetitle = "Default Access Level";
include BASE_PATH . "views/header.php";
  $userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
?>
<div class="container-fluid">
  <div class="row justify-content-between">
    <div class="col-md-3 d-none d-md-block">
      <?php
        $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/myaccount/ProfileEditorLinks.json'));
        echo $list->render('default-access-level');
      ?>
    </div>
    <div class="col-md-9">
      <h1>Default account access level</h1>
      <p class="lead">
        If you have been granted extra access permissions, you can switch between those accounts by selecting your name in the top right corner.
      </p>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['SavedChanges']) && $_SESSION['TENANT-' . app()->tenant->getId()]['SavedChanges']) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>Changes saved successfully.</strong>
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['SavedChanges']); } ?>

      <p>
        Here you can pick which access level is used as your default when you log in.
      </p>

      <form method="post">

        <div class="mb-3">
          <label class="form-label" for="selector">
            Select default access level
          </label>
          <select class="form-select" name="selector" id="selector" required <?php if (sizeof($perms) < 2) { ?>disabled<?php } ?>>
            <option>Choose your default mode</option>
            <?php foreach ($perms as $key => $value) { ?>
            <option <?php if ($key == $default) { ?>selected<?php } ?> value="<?=htmlspecialchars($key)?>"><?=htmlspecialchars($value)?></option>
            <?php } ?>
          </select>
        </div>

        <p>
          <button class="btn btn-success" type="submit" type="submit">
            Save
          </button>
        </p>
      </form>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->useFluidContainer();
$footer->render(); ?>
