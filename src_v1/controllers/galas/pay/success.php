<?php

$db = app()->db;
$tenant = app()->tenant;

$swimsArray = [
  '25Free' => '25&nbsp;Free',
  '50Free' => '50&nbsp;Free',
  '100Free' => '100&nbsp;Free',
  '200Free' => '200&nbsp;Free',
  '400Free' => '400&nbsp;Free',
  '800Free' => '800&nbsp;Free',
  '1500Free' => '1500&nbsp;Free',
  '25Back' => '25&nbsp;Back',
  '50Back' => '50&nbsp;Back',
  '100Back' => '100&nbsp;Back',
  '200Back' => '200&nbsp;Back',
  '25Breast' => '25&nbsp;Breast',
  '50Breast' => '50&nbsp;Breast',
  '100Breast' => '100&nbsp;Breast',
  '200Breast' => '200&nbsp;Breast',
  '25Fly' => '25&nbsp;Fly',
  '50Fly' => '50&nbsp;Fly',
  '100Fly' => '100&nbsp;Fly',
  '200Fly' => '200&nbsp;Fly',
  '100IM' => '100&nbsp;IM',
  '150IM' => '150&nbsp;IM',
  '200IM' => '200&nbsp;IM',
  '400IM' => '400&nbsp;IM'
];

$rowArray = [1, null, null, null, null, null, 2, 1,  null, null, 2, 1, null, null, 2, 1, null, null, 2, 1, null, null, 2];
$rowArrayText = ["Freestyle", null, null, null, null, null, 2, "Backstroke",  null, null, 2, "Breaststroke", null, null, 2, "Butterfly", null, null, 2, "Individual Medley", null, null, 2];

$getEntry = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE EntryID = ? AND members.UserID = ?");

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['CompletedEntryInfo']) || !$_SESSION['TENANT-' . app()->tenant->getId()]['CompletedEntryInfo']) {
  halt(404);
}

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentSuccess']) || !$_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentSuccess']) {
  halt(404);
}

$getEntriesByPI = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE StripePayment = ?");
$getEntriesByPI->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['CompletedEntryInfo']
]);

$getIntent = $db->prepare("SELECT Intent FROM stripePayments WHERE ID = ?");
$getIntent->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['CompletedEntryInfo']
]);
$intentId = $getIntent->fetchColumn();
if ($intentId == null) {
  halt(404);
}

$intent = null;
try {
  \Stripe\Stripe::setApiKey(getenv('STRIPE'));
  $intent = \Stripe\PaymentIntent::retrieve(
    $intentId,
    [
      'stripe_account' => $tenant->getStripeAccount()
    ]
  );
} catch (Exception $e) {
  halt(500);
}

$numFormatter = new NumberFormatter("en", NumberFormatter::SPELLOUT);

$pagetitle = "Payment Success";
include BASE_PATH . "views/header.php";
include BASE_PATH . "controllers/galas/galaMenu.php";
?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas") ?>">Galas</a></li>
        <li class="breadcrumb-item active" aria-current="page">Pay for entries</li>
      </ol>
    </nav>

    <div class="row">
      <div class="col-lg-8">
        <h1>Payment successful</h1>
        <p class="lead mb-0">Your payment was successful</p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <p>A confirmation email is on the way to you.</p>

      <h2>Payment details</h2>

      <ul class="list-group mb-3">
        <?php while ($entry = $getEntriesByPI->fetch(PDO::FETCH_ASSOC)) {
          $notReady = !$entry['EntryProcessed'];
          $galaData = new GalaPrices($db, $entry['GalaID']);
        ?>
          <li class="list-group-item">
            <h3><?= htmlspecialchars($entry['MForename'] . ' ' . $entry['MSurname']) ?> <br><small><?= htmlspecialchars($entry['GalaName']) ?></small></h3>
            <div class="row">
              <div class="col-4 col-sm-5 col-md-4 col-lg-6">
                <p>
                  <a data-bs-toggle="collapse" href="#swims-<?= $entry['EntryID'] ?>" role="button" aria-expanded="false" aria-controls="swims-<?= $entry['EntryID'] ?>">
                    View swims
                  </a>
                </p>
                <div class="collapse" id="swims-<?= $entry['EntryID'] ?>">
                  <ul class="list-unstyled">
                    <?php $count = 0; ?>
                    <?php foreach ($swimsArray as $colTitle => $text) { ?>
                      <?php if ($entry[$colTitle]) {
                        $count++; ?>
                        <li class="row">
                          <div class="col">
                            <?= $text ?>
                          </div>
                          <?php if ($galaData->getEvent($colTitle)->isEnabled()) { ?>
                            <div class="col">
                              &pound;<?= $galaData->getEvent($colTitle)->getPriceAsString() ?>
                            </div>
                          <?php } ?>
                        </li>
                      <?php } ?>
                    <?php } ?>
                </div>
              </div>
              <div class="col text-end">
                <p>
                  <?= mb_convert_case($numFormatter->format($count), MB_CASE_TITLE_SIMPLE) ?> event<?php if ($count != 1) { ?>s<?php } ?>
                </p>

                <p class="mb-0">
                  <strong>Fee &pound;<?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $entry['FeeToPay'])->toScale(2))) ?></strong>
                </p>
              </div>
            </div>
          </li>
        <?php } ?>
        <li class="list-group-item">
          <div class="row align-items-center">
            <div class="col-6">
              <p class="mb-0">
                <strong>Total paid</strong>
              </p>
            </div>
            <div class="col text-end">
              <p class="mb-0">
                <strong>&pound;<?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $intent->amount))->withPointMovedLeft(2)->toScale(2)) ?></strong>
              </p>
            </div>
          </div>
        </li>
      </ul>

      <p>
        <a href="<?= autoUrl("galas") ?>" class="btn btn-success">
          Return to galas
        </a>
      </p>

    </div>
  </div>
</div>


<?php

unset($_SESSION['TENANT-' . app()->tenant->getId()]['CompletedEntries']);
unset($_SESSION['TENANT-' . app()->tenant->getId()]['CompletedEntryInfo']);
unset($_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentSuccess']);

$footer = new \SCDS\Footer();
$footer->render();
