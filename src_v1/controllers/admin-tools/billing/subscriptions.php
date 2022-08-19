<?php

$tenant = app()->tenant;
$stripe = new \Stripe\StripeClient(
  getenv('STRIPE')
);

$subs = $stripe->subscriptions->all(
  [
    'customer' => $tenant->getStripeCustomer()->id,
    'status' => 'active',
    'expand' => ['data.default_payment_method']
  ],
  // ['expand' => ['items.price.product']]
);

// expand items.price.product

$pagetitle = "SCDS Subscriptions";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">Admin</li>
      <li class="breadcrumb-item">Billing</li>
      <li class="breadcrumb-item active" aria-current="page">Subscriptions</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>SCDS Subscriptions</h1>
      <p class="lead">Your current subscriptions.</p>

      <?php if (sizeof($subs->data) > 0) { ?>
        <ul class="list-group">
          <?php foreach ($subs->data as $subscription) { ?>
            <li class="list-group-item">
              <h2>Subscription </h2>

              <p class="mb-0">
                Current period: <?= htmlspecialchars((new DateTime('@' . $subscription->current_period_start, new DateTimeZone('UTC')))->format('H:i d/m/Y T')) ?> - <?= htmlspecialchars((new DateTime('@' . $subscription->current_period_end, new DateTimeZone('UTC')))->format('H:i d/m/Y T')) ?>
              </p>
            </li>
          <?php } ?>
        </ul>
      <?php } ?>

    </div>

    <div class="col">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/admin-tools/list.json'));
      echo $list->render('scds-payments');
      ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
