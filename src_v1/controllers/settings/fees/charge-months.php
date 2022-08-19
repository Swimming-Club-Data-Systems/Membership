<?php

$db = app()->db;

$fluidContainer = true;

//app()->tenant->setKey('SquadFeeMonths', '');

$squadFeeMonths = json_decode(app()->tenant->getKey('SquadFeeMonths'), true);

$pagetitle = "Squad Fee Payment Months";

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
          <li class="breadcrumb-item active" aria-current="page">Billing Months</li>
        </ol>
      </nav>

      <main>
        <h1>Squad Fee Payment Months</h1>
        <p class="lead">Select months where squad fees are not charged.</p>

        <p>In selected months, we won't charge squad fees to parents. Other charges, such as extra fees will still be charged.</p>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success']) && $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success']) { ?>
          <div class="alert alert-success">Changes saved successfully</div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success']);
        } ?>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']) && $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']) { ?>
          <div class="alert alert-danger">Changes could not be saved</div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']);
        } ?>

        <form method="post">

          <?php for ($m = 1; $m <= 12; $m++) {
            $month =  mktime(0, 0, 0, $m, 1); ?>
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="month-<?= htmlspecialchars(date('m', $month)) ?>" name="month-<?= htmlspecialchars(date('m', $month)) ?>" <?php if ($squadFeeMonths != null && bool($squadFeeMonths[date('m', $month)])) { ?>checked<?php } ?>>
                <label class="form-check-label" for="month-<?= htmlspecialchars(date('m', $month)) ?>">
                  No fees in <?= htmlspecialchars(date('F', $month)) ?>
                </label>
              </div>
            </div>
          <?php } ?>

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
