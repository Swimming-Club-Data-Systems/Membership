<?php

$pagetitle = "Help with invoice payments";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl('payments'))?>">Payments</a></li>
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl('payments/invoice-payments'))?>">Invoicing</a></li>
      <li class="breadcrumb-item active" aria-current="page">Help</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>Help with invoice payments</h1>
      <p class="lead">Invoice payments let you manually add a charge or credit to an account</p>

      <p>From time to time you may need to manually add a charge or credit to an account. You can do this by adding a new <strong>invoice payment</strong>.</p>

      <p>An invoice payment allows you to find a user account and create a new charge or credit. The charge/credit is referred to in this help page as an item.</p>

      <p>You can add one item to a user account at a time.</p>

      <p>To add a new item you will need;</p>

      <ul>
        <li>The email address of the user you're going to charge or refund</li>
        <li>A description for the item (which will be shown on the user's statement). Pick a description they will be able to easily understand</li>
        <li>The amount to refund/charge, which must be between &pound;0* and &pound;1000**</li>
        <li>Whether the item is a charge or a credit.</li>
      </ul>

      <p>You'll be asked to confirm the item before it is applied to an account.</p>

      <p>The user will receive an email notification detailing the item and will pay for it/be refunded on their next billing date.</p>

      <p>* You can use an item of &pound;0 if you need to add a descriptive line item to a user's statement but do not need to charge or refund them.</p>

      <p>** Large refunds where the refund will be greater than the total amount to charge on the next billing day will be applied to make the amount due &pound;0. The remainder will be carried over repeatedly until the account has a balance that charged.</p>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();