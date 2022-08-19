<?php

$db = app()->db;
$tenant = app()->tenant;

$getCard = $db->prepare("SELECT `Name`, Last4, Brand, ExpMonth, ExpYear, Funding, PostCode, Line1, Line2, CardName, MethodID, Reusable FROM stripePayMethods INNER JOIN stripeCustomers ON stripeCustomers.CustomerID = stripePayMethods.Customer WHERE User = ? AND stripePayMethods.ID = ?");
$getCard->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $id]);

$card = $getCard->fetch(PDO::FETCH_ASSOC);

if ($card == null) {
  halt(404);
}

$pagetitle = htmlspecialchars(getCardBrand($card['Brand'])) . ' &#0149;&#0149;&#0149;&#0149; ' . htmlspecialchars($card['Last4']);

include BASE_PATH . 'views/header.php';

\Stripe\Stripe::setApiKey(getenv('STRIPE'));
$pm = \Stripe\PaymentMethod::retrieve(
  $card['MethodID'],
  [
    'stripe_account' => $tenant->getStripeAccount()
  ]
);

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("payments") ?>">Payments</a></li>
        <li class="breadcrumb-item"><a href="<?= autoUrl("payments/cards") ?>">Cards</a></li>
        <li class="breadcrumb-item active" aria-current="page">&#0149;&#0149;&#0149;&#0149; <?= htmlspecialchars($card['Last4']) ?></li>
      </ol>
    </nav>

    <div class="row">
      <div class="col-lg-8">
        <div class="row align-items-center mb-2">
          <div class="col-auto">
            <img class="d-dark-none" src="<?= autoUrl("img/stripe/brand-stored-credentials/" . $card['Brand'] . "_light.svg") ?>" style="width:40px;"><img class="d-light-none" src="<?= autoUrl("img/stripe/brand-stored-credentials/" . $card['Brand'] . "_dark.svg") ?>" style="width:48px;"> <span class="visually-hidden"><?= htmlspecialchars(getCardBrand($card['Brand'])) ?></span> <span class="visually-hidden"><?= htmlspecialchars(getCardBrand($card['Brand'])) ?></span>
          </div>
          <div class="col-auto">
            <h1 class="h1 my-0">
              <?= getCardBrand($card['Brand']) ?> &#0149;&#0149;&#0149;&#0149; <?= htmlspecialchars($card['Last4']) ?>
            </h1>
          </div>
        </div>
        <p class="lead mb-0">
          <?= htmlspecialchars(mb_convert_case($card['Funding'], MB_CASE_TITLE)) ?> card
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">

      <h2>Expiry</h2>
      <p>
        Expires at the end of <?= htmlspecialchars(date("F Y", strtotime($card['ExpYear'] . '-' . $card['ExpMonth'] . '-01'))) ?>.
      </p>

      <?php if (bool($card['Reusable'])) { ?>
        <p>
          Depending on your issuing bank, we may be able automatically update your card details when it expires or is replaced. If this is the case, we'll update the last 4 digits and expiry date.
        </p>

        <p>
          If you don't want to have your cards automatically updated, you can opt out of these services by contacting your issuing bank.
        </p>
      <?php } ?>

      <?php if (isset($pm->billing_details->name) || isset($pm->billing_details->address->line1) || isset($pm->billing_details->address->postal_code)) { ?>
        <h2>Billing details</h2>
        <address class="mb-3">
          <?php if (isset($pm->billing_details->name)) { ?>
            <strong><?= htmlspecialchars($pm->billing_details->name) ?><br></strong>
          <?php } ?>
          <?php if (isset($pm->billing_details->address->line1)) { ?>
            <?= htmlspecialchars($pm->billing_details->address->line1) ?><br>
          <?php } ?>
          <?php if (isset($pm->billing_details->address->postal_code)) { ?>
            <?= htmlspecialchars(mb_strtoupper($pm->billing_details->address->postal_code)) ?>
          <?php } ?>
        </address>
      <?php } ?>

      <?php if (bool($card['Reusable'])) { ?>
        <h2>Forget card</h2>
        <p class="lead">Forget this card to remove it from your list</p>
        <p class="mb-5">
          <a href="<?= htmlspecialchars(autoUrl("payments/cards/" . $id . "/delete")) ?>" class="btn btn-danger">
            Forget card
          </a>
        </p>
      <?php } ?>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
