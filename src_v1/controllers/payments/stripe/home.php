<?php

$db = app()->db;
$tenant = app()->tenant;

\Stripe\Stripe::setApiKey(getenv('STRIPE'));
//$paymentsMeths = \Stripe\PaymentMethod::all(["customer" => "cus_FF5F1cnWIA7UAI", "type" => "card"]);

$expMonth = date("m");
$expYear = date("Y");

$getCards = $db->prepare("SELECT stripePayMethods.ID, `Name`, Last4, Brand, ExpMonth, ExpYear, Funding, PostCode, Line1, Line2, CardName FROM stripePayMethods INNER JOIN stripeCustomers ON stripeCustomers.CustomerID = stripePayMethods.Customer WHERE User = ? AND Reusable = ? AND (ExpYear > ? OR (ExpYear = ? AND ExpMonth >= ?)) ORDER BY `Name` ASC");
$getCards->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], 1, $expYear, $expYear, $expMonth]);
$card = $getCards->fetch(PDO::FETCH_ASSOC);

$pagetitle = 'Payment Cards';

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("payments") ?>">Payments</a></li>
        <li class="breadcrumb-item active" aria-current="page">Cards</li>
      </ol>
    </nav>

    <div class="row">
      <div class="col-lg-8">
        <h1>Payment Cards</h1>
        <p class="lead mb-0">Introducing new ways to pay!</p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['PayCardSetupSuccess'])) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>We have added your new card</strong>
          </p>
          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['PayCardSetupSuccessBrand'])) { ?>
            <p class="mb-0">Your <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['PayCardSetupSuccessBrand']) ?> card is now ready.</p>
          <?php } ?>
        </div>
      <?php } ?>
      <?php
      unset($_SESSION['TENANT-' . app()->tenant->getId()]['PayCardSetupSuccessBrand']);
      unset($_SESSION['TENANT-' . app()->tenant->getId()]['PayCardSetupSuccess']);
      ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['CardDeleted'])) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>Card deleted</strong>
          </p>
          <p class="mb-0">
            Your card details will no longer be shown in the list of saved cards.
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['CardDeleted']);
      } ?>

      <div class="accepted-network-logos">
        <p>
          We proudly accept all major credit and debit cards!
        </p>
        <p>
          <?= \SCDS\Checkout\Assets::cardLogos() ?>
        </p>
      </div>

      <?php if ($card != null) { ?>
        <div class="list-group mb-3">
          <?php do { ?>
            <a href="<?= autoUrl("payments/cards/" . $card['ID']) ?>" class="list-group-item list-group-item-action">
              <div class="row align-items-center mb-2">
                <div class="col-auto">
                  <img class="d-dark-none" src="<?= autoUrl("img/stripe/brand-stored-credentials/" . $card['Brand'] . "_light.svg") ?>" style="width:40px;"><img class="d-light-none" src="<?= autoUrl("img/stripe/brand-stored-credentials/" . $card['Brand'] . "_dark.svg") ?>" style="width:40px;"> <span class="visually-hidden"><?= htmlspecialchars(getCardBrand($card['Brand'])) ?></span>
                </div>
                <div class="col-auto">
                  <h2 class="h1 my-0">
                    &#0149;&#0149;&#0149;&#0149; <?= htmlspecialchars($card['Last4']) ?>
                  </h2>
                </div>
              </div>
              <p class="lead">
                <?= htmlspecialchars(mb_convert_case($card['Funding'], MB_CASE_TITLE)) ?> card
              </p>

              <p class="mb-0">
                <span class="text-link-color">
                  Edit card
                </span>
              </p>
            </a>
          <?php } while ($card = $getCards->fetch(PDO::FETCH_ASSOC)); ?>
        </div>
      <?php } else { ?>
        <div class="alert alert-warning">
          You have no payment cards available.
        </div>
      <?php } ?>

      <p>
        <a href="<?= autoUrl("payments/cards/add") ?>" class="btn btn-success">
          Add a card
        </a>
      </p>
      <p>
        We accept Visa, MasterCard and American Express.
      </p>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
