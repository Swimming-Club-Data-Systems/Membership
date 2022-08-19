<?php

$pagetitle = 'Payment Confirmation';

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h1>Welcome to payment confirmation</h1>
      <p class="lead">Confirmation lets you confirm an account balance has been paid when a parent does not use direct debit.</p>

      <p>Completing confirmation helps you keep track of the bigger picture financially and allows parents to see that their payment has been confirmed.</p>

      <p>If a parent paid with the reference given to them on their bill, confirmation is fast and easy. If they didn't, we can find their due payment by asking you a few more questions.</p>

      <?php include 'form-main.php'; ?>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();