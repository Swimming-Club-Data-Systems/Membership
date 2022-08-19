<?php

$month = (int) (new DateTime('now', new DateTimeZone('Europe/London')))->format('n');
$summer = new FeeSummer($month);
$fees = $summer->sumAll();

$pagetitle = 'Payment Amounts - Trial Run';

$total = 0;

$fluidContainer = true;
include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">

  <h1>Payments - Trial Run</h1>
  <p class="lead">
    See estimated payment amounts for each user, if squad fees and other charges were billed today.
  </p>

  <ul class="list-group mb-3">
  <?php foreach ($fees as $row => $data) {
    $total += $data['total'];
    if (sizeof($data['items']['existing']) > 0 || sizeof($data['items']['provisional']) > 0) { ?>
      <li class="list-group-item px-3 pb-0 bg-light">
        <div class="row mb-3">
          <div class="col-sm-9">
            <h2><a href="<?=htmlspecialchars(autoUrl("users/" . $data['user']))?>"><?=htmlspecialchars($data['forename'] . ' ' . $data['surname'])?></a></h2>
          </div>
          <div class="col">
            <div class="row">
              <div class="col-6 text-end">
                Subtotal
              </div>
              <div class="col-6">
                &pound;<?=htmlspecialchars((string) \Brick\Math\BigInteger::of((string) $data['debits'])->toBigDecimal()->withPointMovedLeft(2))?>
              </div>

              <div class="col-6 text-end">
                Credits
              </div>
              <div class="col-6">
                &pound;<?=htmlspecialchars((string) \Brick\Math\BigInteger::of((string) $data['credits'])->toBigDecimal()->withPointMovedLeft(2))?>
              </div>

              <div class="col-6 text-end">
                Taxes
              </div>
              <div class="col-6">
                &pound;0.00
              </div>

              <div class="col-6 text-end">
                <strong>Total</strong>
              </div>
              <div class="col-6">
                <strong>&pound;<?=htmlspecialchars((string) \Brick\Math\BigInteger::of((string) $data['total'])->toBigDecimal()->withPointMovedLeft(2))?></strong>
              </div>
            </div>
          </div>
        </div>

        <!-- Show individual items -->
        <ul class="list-group list-group-flush border-top mx-n3">
          <?php 
          foreach ($data['items']['provisional'] as $item) { ?>
          <li class="list-group-item">
            <?=htmlspecialchars($item['description'])?>, &pound;<?=htmlspecialchars((string) \Brick\Math\BigInteger::of((string) $item['amount'])->toBigDecimal()->withPointMovedLeft(2))?><br>
            <?=htmlspecialchars($item['type'])?>, Provisional amount
          </li>
          <?php } ?>
          <?php 
          foreach ($data['items']['existing'] as $item) { ?>
          <li class="list-group-item">
            <?=htmlspecialchars($item['description'])?>, &pound;<?=htmlspecialchars((string) \Brick\Math\BigInteger::of((string) $item['amount'])->toBigDecimal()->withPointMovedLeft(2))?><br>
            <?=htmlspecialchars($item['type'])?>, Provisional amount
          </li>
          <?php } ?>
        </ul>
      </li>
    <?php }
  } ?>
  </ul>

  <p>
    The expected total income (before transaction fees) is <strong>&pound;<?=htmlspecialchars((string) \Brick\Math\BigInteger::of((string) $total)->toBigDecimal()->withPointMovedLeft(2))?></strong>
  </p>

</div>

<?php 

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();
