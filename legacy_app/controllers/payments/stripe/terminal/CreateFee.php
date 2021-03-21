<?php

$pagetitle = "Pay a fee";

include BASE_PATH . 'views/header.php';

?>

<div class="container">
  <div class="row">
    <div class="col-lg-8">
      <h1>Pay a fee</h1>

      <form method="post">
        <div class="form-group">
          <label for="amount-to-pay">Amount</label>
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <label class="input-group-text">&pound;</label>
            </div>
            <input type="number" class="form-control" id="amount-to-pay" name="amount-to-pay">
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();