<?php

$pagetitle = 'More Details - Payment Confirmation';

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h1>Provide more details</h1>
      <p class="lead">We need more details to be able to find this payment.</p>

      <?php include 'form-more-details.php'; ?>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();