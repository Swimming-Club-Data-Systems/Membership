<?php

// Select payment from list

$pagetitle = 'More Details - Payment Confirmation';

$ids = $_SESSION['TENANT-' . app()->tenant->getId()]['PaymentConfSearch']['id'];

$db = app()->db;
$tenant = app()->tenant;

$getPayments = $db->prepare("SELECT `Date`, `Name`, `Amount`, `Currency`, `Forename`, `Surname` FROM payments INNER JOIN users ON payments.UserID = users.UserID WHERE PaymentID = ? AND `Type` = 'Payment' AND users.Tenant = ?");

if (sizeof($ids) == 0) {
  halt(404);
}

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h1>Select payment</h1>
      <p class="lead">Select the payment from the list below.</p>

      <form action="<?= autoUrl("confirmation/confirm-selected") ?>" method="post">

        <div class="list-group mb-3">

          <?php foreach ($ids as $paymentId) { ?>

            <?php
            $getPayments->execute([
              $paymentId,
              $tenant->getId()
            ]);
            $payment = $getPayments->fetch(PDO::FETCH_ASSOC);
            if ($payment == null) {
              break;
            }
            ?>

            <div class="list-group-item">
              <div class="form-check">
                <input type="radio" id="payment-<?= htmlspecialchars($paymentId) ?>" name="payment" value="<?= htmlspecialchars($paymentId) ?>" class="form-check-input">
                <label class="form-check-label d-block" for="payment-<?= htmlspecialchars($paymentId) ?>">
                  <?= htmlspecialchars(\SCDS\Formatting\Names::format($payment['Forename'], $payment['Surname'])) ?>:
                  <span class="font-monospace">
                    &pound;<?= (string) (\Brick\Math\BigDecimal::of((string) $payment['Amount']))->withPointMovedLeft(2)->toScale(2) ?>, <?= htmlspecialchars($payment['Name']) ?>, <?= htmlspecialchars($payment['Date']) ?>
                  </span>
                </label>
              </div>
            </div>

          <?php } ?>

        </div>

        <p>
          <button class="btn btn-success" type="submit">
            Use selected
          </button>
        </p>
      </form>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
