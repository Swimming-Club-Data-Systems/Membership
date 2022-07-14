<?php

$fluidContainer = true;

$db = app()->db;

$pagetitle = "Swim England and Membership Fee Options";

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
          <li class="breadcrumb-item active" aria-current="page">Fees</li>
        </ol>
      </nav>

      <main>
        <h1>Swim England and Club Membership Fee Management</h1>
        <p class="lead">Set amounts for Swim England membership fees and club membership fees</p>

        <div class="list-group mb-3">
          <a href="<?= autoUrl("settings/fees/multiple-squads") ?>" class="list-group-item list-group-item-action">
            Fees for members in multiple squads
          </a>
          <a href="<?= autoUrl("settings/fees/membership-fees") ?>" class="list-group-item list-group-item-action">
            Club and <?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> membership fees
          </a>
          <a href="<?= autoUrl("settings/fees/charge-months") ?>" class="list-group-item list-group-item-action">
            Months without squad fees
          </a>
          <a href="<?= autoUrl("settings/fees/membership-fee-payment-methods") ?>" class="list-group-item list-group-item-action">
            Payment methods for club and <?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> fees
          </a>
        </div>
      </main>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();
