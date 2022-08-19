<?php

$db = app()->db;
$tenant = app()->tenant;

\Stripe\Stripe::setApiKey(getenv('STRIPE'));

$pagination = new \SCDS\Pagination();
$pagination->records_per_page(10);

$page = $pagination->get_page();

$start = $pagination->get_limit_start();

$url = 'payments/card-transactions?';

$getCount = null;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin' && isset($_GET['users']) && $_GET['users'] == 'all') {
  $getCount = $db->prepare("SELECT COUNT(*) FROM stripePayments INNER JOIN users ON stripePayments.User = users.UserID WHERE users.Tenant = ? AND Paid");
  $getCount->execute([
    $tenant->getId()
  ]);
} else {
  $getCount = $db->prepare("SELECT COUNT(*) FROM stripePayments WHERE User = ? AND Paid");
  $getCount->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
}
$count = $getCount->fetchColumn();

$pagination->records($count);
if ($page > 1 && $start >= $count) {
  halt(404);
}

$payments = null;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin' && isset($_GET['users']) && $_GET['users'] == 'all') {
  $url .= 'users=all&';
  $payments = $db->prepare("SELECT stripePayments.ID, stripePayments.DateTime, stripePayMethods.Brand, stripePayMethods.Last4, stripePayments.Amount, users.Forename, users.Surname FROM ((stripePayments LEFT JOIN stripePayMethods ON stripePayments.Method = stripePayMethods.ID) LEFT JOIN users ON stripePayments.User = users.UserID) WHERE users.Tenant = :tenant AND Paid ORDER BY `DateTime` DESC LIMIT :offset, :num;");
  $payments->bindValue(':tenant', $tenant->getId(), PDO::PARAM_INT);
  $payments->bindValue(':offset', $start, PDO::PARAM_INT);
  $payments->bindValue(':num', 10, PDO::PARAM_INT);
  $payments->execute();
} else {
  $payments = $db->prepare("SELECT stripePayments.ID, stripePayments.DateTime, stripePayMethods.Brand, stripePayMethods.Last4, stripePayments.Amount, users.Forename, users.Surname FROM ((stripePayments LEFT JOIN stripePayMethods ON stripePayments.Method = stripePayMethods.ID) LEFT JOIN users ON stripePayments.User = users.UserID) WHERE User = :user AND Paid ORDER BY `DateTime` DESC LIMIT :offset, :num;");
  $payments->bindValue(':user', $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], PDO::PARAM_INT);
  $payments->bindValue(':offset', $start, PDO::PARAM_INT);
  $payments->bindValue(':num', 10, PDO::PARAM_INT);
  $payments->execute();
}

$pagetitle = 'Card Payment History';

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("payments") ?>">Payments</a></li>
        <li class="breadcrumb-item"><a href="<?= autoUrl("payments/cards") ?>">Cards</a></li>
        <li class="breadcrumb-item active" aria-current="page">History</li>
      </ol>
    </nav>

    <div class="row">
      <div class="col-lg-8">
        <h1>Card payment history</h1>
        <p class="lead mb-0">Previous card payments</p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">

      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
        <?php if (isset($_GET['users']) && $_GET['users'] == 'all') { ?>
          <p>
            <a href="<?= autoUrl("payments/card-transactions") ?>">View only my transactions</a>
          </p>
        <?php } else { ?>
          <p>
            <a href="<?= autoUrl("payments/card-transactions?users=all") ?>">View all user's transactions</a>
          </p>
        <?php } ?>
      <?php } ?>

      <div class="list-group">
        <?php while ($pm = $payments->fetch(PDO::FETCH_ASSOC)) {
          $date = new DateTime($pm['DateTime'], new DateTimeZone('UTC'));
          $date->setTimezone(new DateTimeZone('Europe/London'));
        ?>
          <a href="<?= htmlspecialchars(autoUrl("payments/card-transactions/" . $pm['ID'])) ?>" class="list-group-item list-group-item-action">
            <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
              <p class="h3 mb-3"><?= htmlspecialchars(\SCDS\Formatting\Names::format($pm['Forename'], $pm['Surname'])) ?></p>
            <?php } ?>
            <?php if (isset($pm['Brand'])) { ?>
              <div class="row align-items-center mb-2">
                <div class="col-auto">
                  <img class="accepted-network-logo d-dark-none" src="<?= autoUrl("img/stripe/brand-stored-credentials/" . $pm['Brand'] . "_light.svg") ?>"><img class="accepted-network-logo  d-light-none" src="<?= autoUrl("img/stripe/brand-stored-credentials/" . $pm['Brand'] . "_dark.svg") ?>"> <span class="visually-hidden"><?= htmlspecialchars(getCardBrand($pm['Brand'])) ?></span>
                </div>
                <div class="col-auto">
                  <h2 class="my-0">
                    &#0149;&#0149;&#0149;&#0149; <?= htmlspecialchars($pm['Last4']) ?>
                  </h2>
                </div>
              </div>
            <?php } ?>
            <p class="lead">At <?= $date->format("H:i \o\\n j F Y") ?></p>
            <p class="font-monospace mb-0">&pound;<?= number_format($pm['Amount'] / 100, 2, '.', '') ?></p>
          </a>
        <?php } ?>
      </div>

      <?= $pagination->render() ?>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
