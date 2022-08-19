<?php

$db = app()->db;
$tenant = app()->tenant;

$getExtra = $db->prepare("SELECT * FROM `extras` WHERE `ExtraID` = ? AND Tenant = ?");
$getExtra->execute([
  $id,
  $tenant->getId()
]);
$row = $getExtra->fetch(PDO::FETCH_ASSOC);

if ($row == null) {
  halt(404);
}

$pagetitle = "Editing " . htmlspecialchars($row['ExtraName']);

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('payments')) ?>">Payments</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('payments/extrafees')) ?>">Extras</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('payments/extrafees/' . $id)) ?>"><?= htmlspecialchars($row['ExtraName']) ?></a></li>
      <li class="breadcrumb-item active" aria-current="page">Edit</li>

    </ol>
  </nav>

  <div class="">
    <h1>
      Edit <?= htmlspecialchars($row['ExtraName']) ?>
    </h1>
    <p class="lead">Edit this extra monthly fee.</p>

    <div class="row">
      <div class="col-lg-8">
        <?php
        if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) {
          echo $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'];
          unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
        }
        ?>
        <form method="post" class="needs-validation" novalidate>
          <div class="mb-3">
            <label class="form-label" for="name">Extra Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Enter name" value="<?= htmlspecialchars($row['ExtraName']) ?>" required>
            <div class="invalid-feedback">
              Provide a name for this monthly extra
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="price">Amount</label>
            <div class="input-group mb-3">
              <span class="input-group-text" id="basic-addon3">&pound;</span>
              <input type="number" min="0" step="0.01" class="form-control rounded-end" id="price" name="price" placeholder="0" value="<?= htmlspecialchars($row['ExtraFee']) ?>" required>
              <div class="invalid-feedback">
                Enter an amount for this extra
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label>Monthly payment or refund</label>
            <div class="form-check">
              <input type="radio" id="type-pay" name="pay-credit-type" class="form-check-input" <?php if ($row['Type'] == 'Payment') { ?>checked<?php } ?> value="Payment" required>
              <label class="form-check-label" for="type-pay">Payment</label>
            </div>
            <div class="form-check">
              <input type="radio" id="type-credit" name="pay-credit-type" class="form-check-input" <?php if ($row['Type'] == 'Refund') { ?>checked<?php } ?> value="Refund">
              <label class="form-check-label" for="type-credit">Credit/refund</label>
            </div>
          </div>

          <p class="mb-0">
            <button type="submit" class="btn btn-dark-l btn-outline-light-d">
              Save changes
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();

?>