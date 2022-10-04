<?php

use Brick\Math\RoundingMode;

$db = app()->db;
$tenant = app()->tenant;

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$pagetitle = "Payments Administration";

// require 'GoCardlessSetup.php';

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

$date = new DateTime('now', new DateTimeZone('Europe/London'));
$dateString = $date->format('F Y');

$month = (int) $date->format('n');

$income = $db->prepare("SELECT `Date`, SUM(AMOUNT) AS Total FROM `payments` INNER JOIN users ON users.UserID = payments.UserID WHERE users.Tenant = ? AND `Date` LIKE '%-01' GROUP BY `Date` ORDER BY `Date` DESC LIMIT 8");
$income->execute([
  $tenant->getId()
]);
$income = $income->fetchAll(PDO::FETCH_ASSOC);

$payouts = [];
$cardPayments = [];
$transactionFees = [];
for ($i = ($month - 8); $i < $month; $i++) {
  $monthNum = ($i % 12) + 1;
  $monthText = str_pad($monthNum, 2, "0", STR_PAD_LEFT);

  $getCardPayments = $db->prepare("SELECT SUM(Amount) FROM stripePayments INNER JOIN users ON stripePayments.User = users.UserID WHERE users.Tenant = ? AND stripePayments.Paid AND stripePayments.DateTime LIKE ?");
  $getCardPayments->execute([
    $tenant->getId(),
    '%-' . $monthText . '-%',
  ]);
  $cardPayments[] = (int) $getCardPayments->fetchColumn();

  $getPayouts = $db->prepare("SELECT SUM(Total) FROM ((SELECT Amount AS Total FROM paymentsPayouts WHERE paymentsPayouts.Tenant = ? AND paymentsPayouts.ArrivalDate LIKE ?) UNION ALL (SELECT Amount AS Total FROM stripePayouts WHERE stripePayouts.Tenant = ? AND stripePayouts.ArrivalDate LIKE ?)) AS Combined");
  $getPayouts->execute([
    $tenant->getId(),
    '%-' . $monthText . '-%',
    $tenant->getId(),
    '%-' . $monthText . '-%',
  ]);
  $payouts[] = (int) $getPayouts->fetchColumn();

  $getFees = $db->prepare("SELECT SUM(Fees) FROM ((SELECT Fees FROM stripePayments INNER JOIN users ON stripePayments.User = users.UserID WHERE users.Tenant = ? AND stripePayments.DateTime LIKE ?) UNION ALL (SELECT stripeFee AS Fees FROM payments INNER JOIN users ON payments.UserID = users.UserID WHERE users.Tenant = ? AND payments.Date LIKE ?) UNION ALL (SELECT Fees FROM paymentsPayouts WHERE paymentsPayouts.Tenant = ? AND paymentsPayouts.ArrivalDate LIKE ?)) AS Combined");
  $getFees->execute([
    $tenant->getId(),
    '%-' . $monthText . '-%',
    $tenant->getId(),
    '%-' . $monthText . '-%',
    $tenant->getId(),
    '%-' . $monthText . '-%',
  ]);
  $transactionFees[] = (int) $getFees->fetchColumn();
}

$month = new DateTime('now', new DateTimeZone('Europe/London'));

?>

<div class="front-page mb-n3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Payments</li>
      </ol>
    </nav>

    <h1>Payment Administration</h1>
    <p class="lead">Control Direct Debit Payments</p>

    <?php if ($tenant->getKey('GOCARDLESS_ACCESS_TOKEN') && !$tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT')) { ?>
    <div class="alert alert-info">
      <p class="mb-0">
        <strong>Plan your migration to Stripe for your Direct Debit payments</strong>
      </p>
      <p class="mb-0">
        The GoCardless service is deprecated and will eventually be turned off. To start planning your migration to the Stripe powered service, please talk to SCDS for help and support.
      </p>
    </div>
    <?php } ?>

    <div class="mb-4">
      <h2 class="mb-4">View Fee Status</h2>
      <div class="mb-4">
        <div class="news-grid">

          <a href="<?= htmlspecialchars(autoUrl("payments/history/" . $month->format("Y/m"))) ?>">
            <span class="mb-3">
              <span class="title mb-0">
                All fees for <?= $dateString ?>
              </span>
              <span>
                View current payment status
              </span>
            </span>
            <span class="category">
              Direct Debit
            </span>
          </a>

          <a href="<?= htmlspecialchars(autoUrl("payments/history/squads/" . $month->format("Y/m"))) ?>">
            <span class="mb-3">
              <span class="title mb-0">
                Squad fees for <?= $dateString ?>
              </span>
              <span>
                View current payment status
              </span>
            </span>
            <span class="category">
              Direct Debit
            </span>
          </a>

          <a href="<?= htmlspecialchars(autoUrl("payments/history/extras/" . $month->format("Y/m"))) ?>">
            <span class="mb-3">
              <span class="title mb-0">
                Extra fees for <?= $dateString ?>
              </span>
              <span>
                View current payment status
              </span>
            </span>
            <span class="category">
              Direct Debit
            </span>
          </a>
        </div>
      </div>
    </div>

    <div class="mb-4">
      <h2 class="mb-4">Manual Charges</h2>
      <div class="mb-4">
        <div class="news-grid">

          <a href="<?= htmlspecialchars(autoUrl('payments/estimated-fees')) ?>">
            <span class="mb-3">
              <span class="title mb-0">
                Manual Billing Information
              </span>
              <span>
                View all expected fees for all parents
              </span>
            </span>
            <span class="category">
              Pay
            </span>
          </a>

          <a href="<?= htmlspecialchars(autoUrl('payments/invoice-payments')) ?>">
            <span class="mb-3">
              <span class="title mb-0">
                Invoice payments
              </span>
              <span>
                Manually create charges and credits
              </span>
            </span>
            <span class="category">
              Pay
            </span>
          </a>

          <a href="<?= htmlspecialchars(autoUrl('galas/charges-and-refunds')) ?>">
            <span class="mb-3">
              <span class="title mb-0">
                Charge for gala entries
              </span>
              <span>
                Charge and issue refunds for gala entries
              </span>
            </span>
            <span class="category">
              Pay
            </span>
          </a>
        </div>
      </div>
    </div>

    <div class="mb-4">
      <h2 class="mb-4">Administrative Options</h2>
      <div class="mb-4">
        <div class="news-grid">

          <a href="<?= htmlspecialchars(autoUrl('payments/categories')) ?>">
            <span class="mb-3">
              <span class="title mb-0">
                Payment categories
              </span>
              <span>
                Create categories and assign them to payment items
              </span>
            </span>
            <span class="category">
              Admin
            </span>
          </a>

          <?php if (app()->tenant->getStripeAccount()) { ?>
            <a href="<?= htmlspecialchars(autoUrl('payments/disputes')) ?>">
              <span class="mb-3">
                <span class="title mb-0">
                  Disputes
                </span>
                <span>
                  Dispute information for card and direct debit payments
                </span>
              </span>
              <span class="category">
                Admin
              </span>
            </a>

            <a href="<?= htmlspecialchars(autoUrl('settings/stripe')) ?>">
              <span class="mb-3">
                <span class="title mb-0">
                  Stripe Options
                </span>
                <span>
                  Manage your Stripe account connection
                </span>
              </span>
              <span class="category">
                Admin
              </span>
            </a>
          <?php } ?>

        </div>
      </div>

      <?php if (app()->tenant->getStripeAccount()) { ?>
        <div class="mb-4">
          <h2 class="mb-4">Card Transactions</h2>
          <div class="mb-4">
            <div class="news-grid">

              <a href="<?= htmlspecialchars(autoUrl('payments/cards')) ?>">
                <span class="mb-3">
                  <span class="title mb-0">
                    Payment cards
                  </span>
                  <span>
                    Add and edit your payment cards on file
                  </span>
                </span>
                <span class="category">
                  Cards
                </span>
              </a>

              <a href="<?= htmlspecialchars(autoUrl('payments/card-transactions?users=all')) ?>">
                <span class="mb-3">
                  <span class="title mb-0">
                    Transactions
                  </span>
                  <span>
                    View card transaction history
                  </span>
                </span>
                <span class="category">
                  Cards
                </span>
              </a>

            </div>
          </div>
        <?php } ?>

        <div class="mb-4">
          <?php

          $labels = [];
          for ($i = sizeof($income); $i > 0; $i--) {
            $date = new DateTime($income[$i - 1]['Date'], new DateTimeZone('Europe/London'));
            $labels[] = $date->format("F");
          }

          $dataA = [];
          for ($i = sizeof($income); $i > 0; $i--) {
            $dataA[] = \Brick\Math\BigDecimal::of((string) $income[$i - 1]['Total'])->withPointMovedLeft(2)->toScale(2, RoundingMode::HALF_UP);
          }

          $dataB = [];
          for ($i = sizeof($cardPayments); $i > 0; $i--) {
            $dataB[] = \Brick\Math\BigDecimal::of((string) $cardPayments[$i - 1])->withPointMovedLeft(2)->toScale(2, RoundingMode::HALF_UP);
          }

          $dataC = [];
          for ($i = sizeof($payouts); $i > 0; $i--) {
            $dataC[] = \Brick\Math\BigDecimal::of((string) $payouts[$i - 1])->withPointMovedLeft(2)->toScale(2, RoundingMode::HALF_UP);
          }

          $dataD = [];
          for ($i = sizeof($transactionFees); $i > 0; $i--) {
            $dataD[] = \Brick\Math\BigDecimal::of((string) $transactionFees[$i - 1])->withPointMovedLeft(2)->toScale(2, RoundingMode::HALF_UP);
          }

          $chartColours = chartColours(4);
          $datasets = [
            [
              'label' => 'Total monthly fees (£ Pounds)',
              'data' => $dataA,
              'backgroundColor' => $chartColours[0],
            ],
            [
              'label' => 'Total card payments (£ Pounds)',
              'data' => $dataB,
              'backgroundColor' => $chartColours[1],
            ],
            // [
            //   'label' => 'Total paid out (£ Pounds)',
            //   'data' => $dataC,
            //   'backgroundColor' => $chartColours[2],
            // ],
            // [
            //   'label' => 'Transaction fees (£ Pounds)',
            //   'data' => $dataD,
            //   'backgroundColor' => $chartColours[3],
            // ],
          ];

          $json = json_encode(['labels' => $labels, 'data' => $datasets]);

          ?>
          <h2 class="mb-4">Income Statistics</h2>
          <canvas id="incomeChart" data-data="<?= htmlspecialchars($json) ?>" class="cell mb-1 bg-white"></canvas>
          <p class="small text-muted mb-4">
            This is the amount charged to parents before transaction fees.
          </p>
        </div>
        </div>
    </div>

    <?php $footer = new \SCDS\Footer();
    $footer->addJs('public/js/payments/admin-graph.js');
    $footer->render();
