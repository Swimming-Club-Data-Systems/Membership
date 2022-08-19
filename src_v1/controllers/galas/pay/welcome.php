<?php

$db = app()->db;
$tenant = app()->tenant;

$date = new DateTime('now', new DateTimeZone('Europe/London'));

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

try {
  $entries = $db->prepare("SELECT *, galaEntries.ProcessingFee pFee FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE members.UserID = ? AND (NOT RequiresApproval OR (RequiresApproval AND Approved)) AND NOT Charged AND FeeToPay > 0 AND galas.GalaDate >= ?");
  $entries->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $date->format("Y-m-d")]);
} catch (Exception $e) {
  pre($e);
}
$entry = $entries->fetch(PDO::FETCH_ASSOC);

$currentUser = app()->user;
$notByDirectDebit = $currentUser->getUserBooleanOption('GalaDirectDebitOptOut');

$numFormatter = new NumberFormatter("en", NumberFormatter::SPELLOUT);

$actionUrl = autoUrl('galas/pay-for-entries/checkout-v1');
if (isset($_GET['checkout'])) {
  if ($_GET['checkout'] == 'v1') $actionUrl = autoUrl('galas/pay-for-entries/checkout-v1');
}

$pagetitle = "Pay for entries - Galas";
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
        <h1>Pay for gala entries</h1>
        <p class="lead mb-0">You can pay for gala entries by <?php if (app()->tenant->getBooleanKey('USE_DIRECT_DEBIT')) { ?>direct debit or by <?php } ?>credit or debit card.</p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <div class="accepted-network-logos">
        <p>
          We proudly accept all major credit and debit cards!
        </p>
        <p>
          <?= \SCDS\Checkout\Assets::networkLogos() ?>
        </p>
      </div>

      <?php if (bool($notByDirectDebit) || !app()->tenant->getBooleanKey('ENABLE_BILLING_SYSTEM')) { ?>
        <p>
          You must pay for your entries by card or any other accepted method.
        </p>
      <?php } else if (!app()->tenant->getBooleanKey('ENABLE_BILLING_SYSTEM')) { ?>
        <p>
          You must pay for your entries by card or any other accepted method.
        </p>
      <?php } else { ?>
        <p>
          If you don't make a payment by card, you'll be automatically charged for gala entries as part of your next direct debit payment after the gala coordinator submits the entries to the host club.
        </p>
      <?php } ?>

      <form action="<?= htmlspecialchars($actionUrl) ?>" method="post">
        <?php if ($entry != null) { ?>
          <h2>Select entries to pay for</h2>
          <p class="">Select which galas you would like to pay for. <strong>You can pay for all, some or just one of your gala entries in a single payment.</strong></p>

          <ul class="list-group mb-3">
            <?php do { ?>
              <?php $notReady = !$entry['EntryProcessed']; ?>
              <?php $galaData = new GalaPrices($db, $entry['GalaID']); ?>
              <li class="list-group-item">
                <h3><?= htmlspecialchars(\SCDS\Formatting\Names::format($entry['MForename'], $entry['MSurname'])) ?> for <?= htmlspecialchars($entry['GalaName']) ?></h3>
                <div class="row">
                  <div class="col-sm-5 col-md-4 col-lg-6">
                    <p class="mb-0">
                      <?= htmlspecialchars($entry['MForename']) ?> is entered in;
                    </p>
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
                    </ul>
                    <?php if ($entry['pFee'] > 0) { ?>
                      <div class="row mb-0 mt-3">
                        <div class="col">
                          Processing fee
                        </div>
                        <div class="col">
                          <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($entry['pFee']), 'GBP')) ?>
                        </div>
                      </div>
                    <?php } ?>
                  </div>
                  <div class="col">
                    <div class="d-sm-none mb-3"></div>
                    <p>
                      <?= mb_convert_case($numFormatter->format($count),   MB_CASE_TITLE_SIMPLE) ?> event<?php if ($count != 1) { ?>s<?php } ?>
                    </p>

                    <?php if ($notReady) { ?>
                      <p>
                        Once you pay for this entry, you won't be able to edit it.
                      </p>
                    <?php } ?>

                    <div class="mb-3">
                      <div class="form-check">
                        <input type="checkbox" id="<?= $entry['EntryID'] ?>-pay" name="<?= $entry['EntryID'] ?>-pay" class="form-check-input">
                        <label class="form-check-label" for="<?= $entry['EntryID'] ?>-pay">Pay for this entry</label>
                      </div>
                    </div>

                    <!-- USER INPUT IS LEGACY TO BE REMOVED IN FUTURE ONCE OLD GALAS CLEAR -->
                    <div class="mb-3 mb-0">
                      <label class="form-label" for="<?= $entry['EntryID'] ?>-amount">
                        Amount to pay
                      </label>
                      <div class="input-group">
                        <div class="input-group-text font-monospace">&pound;</div>
                        <input type="number" pattern="[0-9]*([\.,][0-9]*)?" class="form-control font-monospace" id="<?= $entry['EntryID'] ?>-amount" name="<?= $entry['EntryID'] ?>-amount" placeholder="0.00" value="<?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $entry['FeeToPay'])->toScale(2))) ?>" min="0" max="150" step="0.01" <?php if ($entry['GalaFeeConstant']) { ?>readonly<?php } ?>>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
            <?php } while ($entry = $entries->fetch(PDO::FETCH_ASSOC)); ?>
          </ul>

          <div class="alert alert-info">
            <p class="mb-0">
              <strong>Need to pay for more than one gala entry?</strong>
            </p>
            <p class="mb-0">
              Select all entries you wish to pay for to pay for all in one payment.
            </p>
          </div>

          <p>
            <button type="submit" class="btn btn-success">
              Proceed to payment
            </button>
          </p>
        <?php } else { ?>
          <div class="alert alert-warning">
            <p class="mb-0">
              <strong>You have no entries to pay for</strong>
            </p>
          </div>
        <?php } ?>
      </form>
    </div>
  </div>
</div>


<?php

$footer = new \SCDS\Footer();
$footer->render();
