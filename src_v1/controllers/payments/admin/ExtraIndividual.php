<?php

require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$db = app()->db;
$tenant = app()->tenant;

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];

$extra = $db->prepare("SELECT * FROM extras WHERE ExtraID = ? AND Tenant = ?");
$extra->execute([
  $id,
  $tenant->getId()
]);
$row = $extra->fetch(PDO::FETCH_ASSOC);

if ($row == null) {
  halt(404);
}

$squads = $db->prepare("SELECT * FROM `squads` WHERE Tenant = ? ORDER BY `SquadFee` DESC, `SquadName` ASC");
$squads->execute([
  $tenant->getId()
]);

$pagetitle = htmlspecialchars($row['ExtraName']) . " - Extras";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('payments')) ?>">Payments</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('payments/extrafees')) ?>">Extras</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($row['ExtraName']) ?></li>
    </ol>
  </nav>

  <div class="row align-items-center mb-3">
    <div class="col-md-8">
      <h1><?= htmlspecialchars($row['ExtraName']) ?> <small>&pound;<?= htmlspecialchars(number_format($row['ExtraFee'], 2)) ?>/month (<?php if ($row['Type'] == 'Payment') { ?>payment<?php } else { ?>credit/refund<?php } ?>)</small></h1>
    </div>
    <div class="col text-sm-end">
      <a href="<?= htmlspecialchars(autoUrl("payments/extrafees/$id/edit")) ?>" class="btn btn-dark-l btn-outline-light-d">Edit</a>
      <a href="<?= htmlspecialchars(autoUrl("payments/extrafees/$id/delete")) ?>" class="btn btn-danger">Delete</a>
    </div>
  </div>
  <div class="row">
    <div class="col order-md-1 mb-3">
      <div class="card">
        <div class="card-header">
          Add members to extra
        </div>
        <form class="card-body">
          <div class="mb-3">
            <label class="form-label" for="squadSelect">Select squad</label>
            <select class="form-select" id="squadSelect" name="squadSelect">
              <option selected value="" disabled>Choose...</option>
              <option value="all-members">All members</option>
              <?php while ($squadsRow = $squads->fetch(PDO::FETCH_ASSOC)) { ?>
                <option value="<?= htmlspecialchars($squadsRow['SquadID']) ?>">
                  <?= htmlspecialchars($squadsRow['SquadName']) ?>
                </option>
              <?php } ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="swimmerSelect">Select member</label>
            <select disabled class="form-select" id="swimmerSelect" name="swimmerSelect">
              <option value="null " selected>Please select a squad</option>
            </select>
          </div>
          <button disabled type="button" class="btn btn-success" id="addSwimmer" data-ajax-url="<?= htmlspecialchars(autoUrl("payments/extrafees/ajax/" . $id)) ?>">
            Add member to extra
          </button>
          <div id="status">
          </div>
        </form>
      </div>
    </div>
    <div class="col-md-6 order-md-0">
      <div id="output">
        <div class="ajaxPlaceholder">
          <span class="h1 d-block">
            <i class="fa fa-spin fa-circle-o-notch" aria-hidden="true"></i>
            <br>Loading Content
          </span>If content does not display, please turn on JavaScript
        </div>
      </div>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/payments/ExtraMembers.js");
$footer->render();
