<?php

$pagetitle = "Invoice Payments";

include BASE_PATH . 'views/header.php';

?>

<div class="front-page mb-n3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl('payments'))?>">Payments</a></li>
        <li class="breadcrumb-item active" aria-current="page">Invoicing</li>
      </ol>
    </nav>

    <div class="row">
      <div class="col-lg-8">
        <h1>Invoice payments</h1>
        <p class="lead">Add a new payment or credit to an account</p>

        <p>From time to time you may need to manually add a charge or credit to an account. You can do this by adding a new <strong>invoice payment</strong>.</p>
      
      </div>

      <div class="col-12">
        <div class="news-grid">
          <a href="<?=htmlspecialchars(autoUrl('payments/invoice-payments/new'))?>" class="">
            <span class="mb-3">
              <span class="title mb-0">
                New invoice payment
              </span>
              <span>
                Charge or credit an account
              </span>
            </span>
            <span class="category">
              Pay
            </span>
          </a>

          <a href="<?=htmlspecialchars(autoUrl('payments/invoice-payments/help'))?>" class="">
            <span class="mb-3">
              <span class="title mb-0">
                Help
              </span>
              <span>
                Get help with invoice payments
              </span>
            </span>
            <span class="category">
              Pay
            </span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();