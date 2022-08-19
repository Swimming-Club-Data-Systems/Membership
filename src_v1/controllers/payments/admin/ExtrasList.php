<?php

require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$db = app()->db;
$tenant = app()->tenant;

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$pagetitle = "Extras";

$extras = $db->prepare("SELECT * FROM `extras` WHERE Tenant = ? ORDER BY `ExtraName` ASC");
$extras->execute([
  $tenant->getId()
]);
$row = $extras->fetch(PDO::FETCH_ASSOC);

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

 ?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl('payments'))?>">Payments</a></li>
      <li class="breadcrumb-item active" aria-current="page">Extras</li>
    </ol>
  </nav>

  <div class="">
  	<h1>Extras</h1>
    <p class="lead">Extras include CrossFit - Fees paid in addition to Squad Fees</p>
    <p>All extras are billed on a monthly basis</p>
    <?php if ($row != null) { ?>
      <div class="table-responsive-md">
        <table class="table table-light">
          <thead">
            <tr>
              <th>Extra</th>
              <th>Cost</th>
            </tr>
          </thead>
          <tbody>
          <?php do { ?>
            <tr>
              <td>
                <a href="<?=autoUrl("payments/extrafees/" . htmlspecialchars($row['ExtraID']))?>">
                  <?=htmlspecialchars($row['ExtraName'])?>
                </a>
              </td>
              <td>&pound;<?=htmlspecialchars($row['ExtraFee'])?><?php if ($row['Type'] == 'Refund') { ?> (credit/refund)<?php } ?></td>
            </tr>
          <?php } while ($row = $extras->fetch(PDO::FETCH_ASSOC)); ?>
        </tbody>
      </table>
    </div>
    <?php } else { ?>
    <div class="alert alert-info">
      <strong>There are no extras available</strong>
    </div>
    <?php } ?>
    <p class="mb-0">
      <a href="<?=autoUrl("payments/extrafees/new")?>"
        class="btn btn-dark-l btn-outline-light-d">
        Add New Extra
      </a>
    </p>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
