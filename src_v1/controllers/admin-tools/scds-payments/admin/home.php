<?php

$pagetitle = "Payments - SCDS";

$tenant = app()->adminCurrentTenant;
$user = app()->adminCurrentUser;

include BASE_PATH . "views/root/head.php";

?>

<div class="container-xl">

  <?php include 'nav.php'; ?>

  <div class="row py-3">
    <div class="col-lg-8 col-md-10">
      <div class="bg-primary text-white p-4 mb-4 d-inline-block rounded">
        <h1 class="">Payments</h1>
        <p class="mb-0">Manage your club's monthly payments to SCDS</p>
      </div>

      <!-- <div class="list-group">
        <a href="<?= htmlspecialchars(autoUrl('admin/register')) ?>" class="list-group-item list-group-item-action">
          Add Tenant
        </a>
        <a href="<?= htmlspecialchars(autoUrl('admin/notify')) ?>" class="list-group-item list-group-item-action">
          Notify Usage
        </a>
      </div> -->

      <p class="lead">
        SCDS are introducing a new payment for clubs, to replace the current manual invoicing system.
      </p>

      <p>
        The intention is to launch the new system as soon as possible to save time for everyone. Once the system is in place, we will automatically take the monthly fee for the membership system by Direct Debit.
      </p>

      <p>
        This system will use Stripe to handle payments, just like the payment system used by customer clubs.
      </p>

      <p>
        In this section of the membership system, you will be able to;
      </p>

      <ul>
        <li>Set up or modify your club's direct debit instruction</li>
        <li>Your estimated next bill</li>
        <li>Previous bills and their status</li>
      </ul>

      <p>
        In future, if we add Pay As You Go services, such as SMS functionality, you'll also be able to top up your club's account here, using a club credit or debit card.
      </p>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();

?>