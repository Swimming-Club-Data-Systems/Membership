<?php

use function GuzzleHttp\json_decode;

$db = app()->db;
$tenant = app()->tenant;

$getSquads = $db->prepare("SELECT SquadName, SquadID, SquadFee FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, SquadName ASC;");
$getSquads->execute([
  $tenant->getId(),
]);
$squad = $getSquads->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Tier 3 Billing";

$date = new DateTime('now', new DateTimeZone('Europe/London'));
$dateToday = clone $date;

$fees = $tenant->getKey('TIER3_SQUAD_FEES');
if ($fees) {
  $fees = json_decode($fees, true);
  $date = new DateTime($fees['eighteen_by'], new DateTimeZone('Europe/London'));
}

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin")) ?>">Admin</a></li>
      <li class="breadcrumb-item active" aria-current="page">Tier 3</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>Tier 3 Billing</h1>
      <p class="lead">Tier 3 Squad Fees.</p>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['FormSuccess'])) { ?>
        <div class="alert alert-success">
          Saved
        </div>
      <?php
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['FormSuccess']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['FormError'])) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>An error occurred</strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['FormError']) ?>
          </p>
        </div>
      <?php
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['FormError']);
      } ?>

      <?php if ($fees) { ?>
        <p>
          <a href="<?= htmlspecialchars(autoUrl('admin/tier-3/members')) ?>" class="btn btn-primary">See affected members</a>
        </p>
      <?php } ?>

      <?php if ($squad) { ?>
        <form method="post" class="needs-validation" novalidate>

          <div class="mb-3">
            <label class="form-label" for="date-eighteen">Members aged 18 <strong>on or before</strong>...</label>
            <input type="date" name="date-eighteen" id="date-eighteen" class="form-control" max="<?= htmlspecialchars($dateToday->format('Y-m-d')) ?>" required value="<?= htmlspecialchars($date->format('Y-m-d')) ?>">
            <div class="invalid-feedback">
              Please provide a date
            </div>
          </div>

          <div class="list-group mb-3" id="fee-form">
            <?php do {
              $fee = \Brick\Math\BigDecimal::of((string) $squad['SquadFee'])->toScale(2);
            ?>
              <div class="list-group-item">
                <p class="mb-2">
                  <strong><?= htmlspecialchars($squad['SquadName']) ?></strong>
                </p>
                <div class="row align-items-start">
                  <!-- Normal -->
                  <div class="col">
                    <div class="mb-3 mb-0">
                      <label class="form-label" for="<?= htmlspecialchars('normal-price-' . $squad['SquadID']) ?>">Normal Price</label>
                      <input class="form-control" type="number" name="<?= htmlspecialchars('normal-price-' . $squad['SquadID']) ?>" id="<?= htmlspecialchars('normal-price-' . $squad['SquadID']) ?>" value="<?= htmlspecialchars($fee) ?>" disabled>
                    </div>
                  </div>

                  <!-- Discount -->
                  <div class="col">
                    <div class="mb-3 mb-0">
                      <label class="form-label" for="<?= htmlspecialchars('discount-amount-' . $squad['SquadID']) ?>">Discount amount</label>
                      <input class="form-control discount-price-boxes" type="number" name="<?= htmlspecialchars('discount-amount-' . $squad['SquadID']) ?>" id="<?= htmlspecialchars('discount-amount-' . $squad['SquadID']) ?>" value="<?php if (isset($fees['squads'][(string) $squad['SquadID']])) { ?><?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $fees['squads'][(string) $squad['SquadID']]))->withPointMovedLeft(2)->toScale(2)) ?><?php } else { ?><?= htmlspecialchars('0.00') ?><?php } ?>" min="0" max="<?= htmlspecialchars($fee) ?>" required data-bs-target="<?= htmlspecialchars('new-price-' . $squad['SquadID']) ?>" data-fee="<?= htmlspecialchars($fee) ?>" step="0.01">
                      <div class="invalid-feedback">
                        Please set a discount amount which is between &pound;0 and £<?= htmlspecialchars($squad['SquadFee']) ?>
                      </div>
                    </div>
                  </div>

                  <!-- New amount -->
                  <div class="col">
                    <div class="mb-3 mb-0">
                      <label class="form-label" for="<?= htmlspecialchars('new-price-' . $squad['SquadID']) ?>">Discounted Price</label>
                      <input class="form-control" type="number" name="<?= htmlspecialchars('new-price-' . $squad['SquadID']) ?>" id="<?= htmlspecialchars('new-price-' . $squad['SquadID']) ?>" disabled>
                    </div>
                  </div>
                </div>
              </div>
            <?php } while ($squad = $getSquads->fetch(PDO::FETCH_ASSOC)); ?>
          </div>

          <?= \SCDS\CSRF::write(); ?>

          <p>
            <button type="submit" class="btn btn-success">
              Save
            </button>
          </p>
        </form>

      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>There are no squads to display</strong>
          </p>
          <p class="mb-0">
            Add a squad to get started.s
          </p>
        </div>
      <?php } ?>

    </div>

    <div class="col">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/admin-tools/list.json'));
      echo $list->render('tier-three');
      ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/numerical/bignumber.min.js");
$footer->addJS("js/admin/tier-3.js");
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
