<?php

$db = app()->db;
$paymentMethods = $db->prepare("SELECT stripePayMethods.ID, `Name`, Last4, Brand, ExpMonth, ExpYear, Funding, PostCode, Line1, Line2, CardName FROM stripePayMethods INNER JOIN stripeCustomers ON stripeCustomers.CustomerID = stripePayMethods.Customer WHERE User = ?");
$paymentMethods->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);

$pagetitle = "Pay a fee";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h1>Pay a fee</h1>

      <form method="post">
        <div class="mb-3">
          <label class="form-label" for="amount-to-pay">Amount</label>
          <div class="input-group mb-3">
            <label class="input-group-text">&pound;</label>
            <input type="number" pattern="[0-9]*([\.,][0-9]*)?" class="form-control font-monospace" placeholder="0.00" min="0" step="0.01" id="amount-to-pay" name="amount-to-pay">
          </div>
        </div>

        <div class="mb-3">
        <label>Select a card</label>
        <?php while ($pm = $paymentMethods->fetch(PDO::FETCH_ASSOC)) { ?>
        <div class="form-check">
          <input type="radio" id="select-card-<?=$pm['ID']?>" name="select-card" class="form-check-input" value="<?=$pm['ID']?>">
          <label class="form-check-label" for="select-card-<?=$pm['ID']?>">
            <i class="fa <?=htmlspecialchars(getCardFA($pm['Brand']))?>" aria-hidden="true"></i> <span class="visually-hidden"><?=htmlspecialchars($pm['Brand'])?></span> <?=htmlspecialchars($pm['Name'] . ' (Card ending ' . $pm['Last4'] . ')')?>
          </label>
        </div>
        <?php } ?>
        </div>

        <p>
          <button type="submit" class="btn btn-success">
            Go
          </button>
        </p>
      </form>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();