<?php

$db = app()->db;

$fluidContainer = true;

//app()->tenant->setKey('SquadFeeMonths', '');

// $discounts = json_decode(app()->tenant->getKey('MembershipDiscounts'), true);

// foreach ($discounts['CLUB'] as $key => $value) {
//   if ($value == null) {
//     $discounts['CLUB'][$key] = 0;
//   }
// }

// foreach ($discounts['ASA'] as $key => $value) {
//   if ($value == null) {
//     $discounts['ASA'][$key] = 0;
//   }
// }

$pagetitle = "Membership Fee Discounts";

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
          <li class="breadcrumb-item active" aria-current="page">Discounts</li>
        </ol>
      </nav>

      <main>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>This feature is currently disabled</strong>
          </p>
          <p class="mb-0">
            We're sorry for any inconvenience caused.
          </p>
        </div>
      </main>

      <!-- <main>
        <h1>Membership Fee Discounts</h1>
        <p class="lead">Apply disounts to club and Swim England membership fees.</p>

        <p>Discounts to Swim England membership fees are applied equally to all membership levels.</p>

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
            <h2><?= htmlspecialchars(date('F', $month)) ?></h2>
            <div class="row">
              <div class="col-lg-6">
                <div class="mb-3">
                  <label class="form-label" for="se-<?= htmlspecialchars(date('m', $month)) ?>">
                    Swim England Membership Discount
                  </label>
                  <div class="input-group">
                    <input type="number" min="0" max="100" class="form-control font-monospace" id="se-<?= htmlspecialchars(date('m', $month)) ?>" name="se-<?= htmlspecialchars(date('m', $month)) ?>" placeholder="0" value="<?= htmlspecialchars($discounts['ASA'][date('m', $month)]) ?>">
                    <div class="input-group-append">
                      <label class="input-group-text">%</label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-6">
                <div class="mb-3">
                  <label class="form-label" for="club-<?= htmlspecialchars(date('m', $month)) ?>">
                    Club Membership Discount
                  </label>
                  <div class="input-group">
                    <input type="number" min="0" max="100" class="form-control font-monospace" id="club-<?= htmlspecialchars(date('m', $month)) ?>" name="club-<?= htmlspecialchars(date('m', $month)) ?>" placeholder="0" value="<?= htmlspecialchars($discounts['CLUB'][date('m', $month)]) ?>">
                    <div class="input-group-append">
                      <label class="input-group-text">%</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php } ?>

          <p>
            <button class="btn btn-success" type="submit">
              Save
            </button>
          </p>
        </form>
      </main> -->
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();
