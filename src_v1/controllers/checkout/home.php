<?php

$tenant = app()->tenant;

$pagetitle = 'Checkout';

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('payments')) ?>">Payments</a></li>
        <li class="breadcrumb-item active" aria-current="page">Checkout</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>Welcome to SCDS Checkout</h1>
        <p class="lead mb-0">
          SCDS Checkout is the new single checkout service for on-session customer payments.
        </p>
      </div>
      <div class="col">
        <img src="<?= htmlspecialchars(autoUrl('img/corporate/scds.png')) ?>" class="img-fluid ms-auto d-none d-lg-flex rounded" alt="SCDS Logo" width="75" height="75">
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">

      <h2>What is SCDS Checkout?</h2>

      <p class="lead">
        SCDS Checkout is a new service which will unify our on-session checkout pages.
      </p>

      <p>
        Currently, the gala entry service and the membership registration and renewal service implement their own checkout pages. SCDS Checkout will replace these and support new payment facilities for other services such as pay as you go sessions, teaching sessions and more.
      </p>

      <h2>What payment methods will be accepted?</h2>

      <p>
        Our underlying payment infrastructure allows us to accept an unbelievably wide number of international payment methods. We will however initially focus on accepting card and direct debit, as now.
      </p>

      <p>
        We will accept Visa, MasterCard, American Express, Maestro, Discover, and Diner's Club payment cards. This covers all types of card - credit, debit, charge, prepaid, business or personal.
      </p>

      <p class="accepted-network-logos">
        <?= \SCDS\Checkout\Assets::cardLogos() ?>
      </p>

      <h2>Will SCDS Checkout support in-person card payments?</h2>

      <p>
        SCDS Checkout will not support in-person payments at first, but we hope to start supporting these in future with terminal and QR code based systems.
      </p>

      <h2>Is SCDS Checkout secure?</h2>

      <p>
        Yes! SCDS Checkout is very secure. SCDS and our underlying payment providers are PCI DSS compliant and neither SCDS nor <?= htmlspecialchars($tenant->getName()) ?> will ever see your card or bank details.
      </p>

      <p>
        While SCDS and our underlying payment providers are PCI DSS compliant, we can not vouch for <?= htmlspecialchars($tenant->getName()) ?>'s compliance. Please check with a member of staff from <?= htmlspecialchars($tenant->getName()) ?> if you have any concerns.
      </p>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
