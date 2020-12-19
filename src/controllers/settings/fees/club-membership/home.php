<?php

$db = app()->db;
$tenant = app()->tenant;

$getClasses = $db->prepare("SELECT `ID`, `Name`, `Description` FROM `clubMembershipClasses` WHERE `Tenant` = ? ORDER BY `Name` ASC");
$getClasses->execute([
  $tenant->getId(),
]);
$class = $getClasses->fetch(PDO::FETCH_ASSOC);

$fluidContainer = true;

$pagetitle = "Club Membership Fee Options (V2)";

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
      <main>
        <h1>Club Membership Fee Management (V2)</h1>
        <p class="lead">Set amounts for club membership fees</p>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success']) && $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success']) { ?>
          <div class="alert alert-success">Changes saved successfully</div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success']);
        } ?>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']) && $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']) { ?>
          <div class="alert alert-danger">Changes could not be saved</div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']);
        } ?>

        <p>
          Welcome to the new Club Membership Fee settings page. We now support multiple types of annual membership fee.
        </p>

        <p>
          This allows clubs to charge different members, such as masters or club volunteers at a different rate to other members.
        </p>

        <?php if ($class) { ?>

        <div class="list-group mb-3">
          <?php do { ?>
          <a href="<?= htmlspecialchars(autoUrl('settings/fees/membership-fees/' . $class['ID'])) ?>" class="list-group-item list-group-item-action">
            <p class="mb-0"><strong><?= htmlspecialchars($class['Name']) ?></strong></p>
          </a>
          <?php } while ($class = $getClasses->fetch(PDO::FETCH_ASSOC)); ?>
        </div>

        <?php } else { ?>
          <div class="alert alert-warning">
            <p class="mb-0">
              <strong>There are no membership fee classes available</strong>
            </p>
          </div>
        <?php } ?>

        <p>
          <a href="<?= htmlspecialchars(autoUrl('settings/fees/membership-fees/new')) ?>" class="btn btn-success">
            Add new
          </a>
        </p>
      </main>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();