<?php

$db = app()->db;
$tenant = app()->tenant;

$userInfo = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile, RR FROM users WHERE Tenant = ? AND UserID = ? AND Active");
$userInfo->execute([
  $tenant->getId(),
  $id
]);

$info = $userInfo->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

// Get Stripe direct debit info
$getStripeDD = $db->prepare("SELECT stripeMandates.ID, Mandate, Last4, SortCode, `Address`, Reference, `URL`, `Status` FROM stripeMandates INNER JOIN stripeCustomers ON stripeMandates.Customer = stripeCustomers.CustomerID WHERE stripeCustomers.User = ? AND (`Status` = 'accepted' OR `Status` = 'pending') ORDER BY CreationTime DESC LIMIT 1;");
if (stripeDirectDebit()) {
  $getStripeDD->execute([
    $id
  ]);
}
$stripeDD = $getStripeDD->fetch(PDO::FETCH_ASSOC);

$bankName = $bank = $has_logo = $logo_path = null;
$hasGC = false;
if (userHasMandates($id)) {
  $bankName = mb_strtoupper(bankDetails($id, "account_holder_name"));
  if ($bankName != "UNKNOWN") {
    $bankName = $bankName . ', ';
  } else {
    $bankName = null;
  }
  $bank = mb_strtoupper(bankDetails($id, "bank_name"));
  $logo_path = getBankLogo($bank);
  $hasGC = true;
}

$dateTime = new DateTime('first day of this month', new DateTimeZone('Europe/London'));
$ms = $dateTime->format('Y-m');
$date = $dateTime->format('Y-m-d');

$sql = $db->prepare("SELECT COUNT(*) FROM `paymentMonths` WHERE Tenant = ? AND MonthStart = ? ORDER BY `Date` DESC LIMIT 1;");
$sql->execute([
  $tenant->getId(),
  $ms,
]);
$monthExists = $sql->fetchColumn() > 0;

$getPending = $db->prepare("SELECT `PaymentID` `id`, `Date` `date`, `Name` `name`, `Amount` `amount`, `Currency` `currency`, `Type` `type` FROM `paymentsPending` WHERE `UserID` = ? AND `PMkey` IS NULL AND `Status` = 'Pending' ORDER BY `Date` DESC");
$getPending->execute([$id]);
$item = $getPending->fetch(PDO::FETCH_OBJ);

$getSquadMetadata = $db->prepare("SELECT members.MemberID memberId, members.MForename forename, members.MSurname surname, squads.SquadName squad, squads.SquadID squadId, squads.SquadFee fee FROM squads INNER JOIN squadMembers ON squads.SquadID = squadMembers.Squad INNER JOIN members ON members.MemberID = squadMembers.Member WHERE members.UserID = ? AND squadMembers.Paying;");
$getSquadMetadata->execute([
  $id,
]);
$squadFee = $getSquadMetadata->fetch(PDO::FETCH_OBJ);

$getExtraMetadata = $db->prepare("SELECT members.MemberID memberId, members.MForename forename, members.MSurname surname, extras.ExtraName extra, extras.ExtraID extraId, extras.ExtraFee fee, extras.Type `type` FROM ((members INNER JOIN `extrasRelations` ON members.MemberID = extrasRelations.MemberID) INNER JOIN `extras` ON extras.ExtraID = extrasRelations.ExtraID) WHERE members.UserID = ? ORDER BY members.MForename ASC, members.MSurname ASC;");
$getExtraMetadata->execute([
  $id,
]);
$extraFee = $getExtraMetadata->fetch(PDO::FETCH_OBJ);

?>

<p>
  We can start an early Direct Debit payment collection for <?= htmlspecialchars($info['Forename']) ?>. This allows you to take additional fees earlier than scheduled.
</p>

<?php if ($monthExists && ($item || $squadFee || $extraFee)) { ?>

  <form id="trigger-early-payment-form" class="needs-validation" novalidate data-submit-url="<?= htmlspecialchars(autoUrl("users/$id/direct-debit/force-run-submission")) ?>">

    <p>
      Please select the pending payment items you would like to include in this Direct Debit charge.
    </p>

    <p>
      The minimum (total) amount you may charge is &pound;1 (1GBP). This is due to Direct Debit scheme rules.
    </p>

    <p>
      <strong>Beware:</strong> Squad and extra fees may already have been paid this month. Check before you charge.
    </p>

    <ul class="list-group rounded-0 mb-3" id="payment-selector">
      <?php if ($squadFee) { ?>
        <li class="list-group-item bg-light fw-bold">
          Squad Fees
        </li>
        <?php do {

          $amount = $squadFee->fee;
          // if ($item->type == 'Refund') $amount = 0 - $amount;

        ?>
          <li class="list-group-item">
            <div class="row">
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" name="<?= htmlspecialchars("squad-fee-" . $squadFee->squadId . "-" . $squadFee->memberId) ?>" id="<?= htmlspecialchars("squad-fee-" . $squadFee->squadId . "-" . $squadFee->memberId) ?>" data-amount="<?= htmlspecialchars(MoneyHelpers::decimalToInt($amount)) ?>">
                  <label class="form-check-label fw-bold" for="<?= htmlspecialchars("squad-fee-" . $squadFee->squadId . "-" . $squadFee->memberId) ?>">
                    <div><?= htmlspecialchars($squadFee->squad) ?> (<?= htmlspecialchars(\SCDS\Formatting\Names::format($squadFee->forename, $squadFee->surname)) ?>)</div>
                    <div><?= htmlspecialchars(MoneyHelpers::formatCurrency((float) $amount, 'GBP')) ?></div>
                  </label>
                </div>
              </div>
              <div class="col-auto text-end">
                <?= htmlspecialchars((new DateTime('first day of this month', new DateTimeZone('Europe/London')))->format('d/m/Y')) ?>
              </div>
            </div>
          </li>
        <?php } while ($squadFee = $getSquadMetadata->fetch(PDO::FETCH_OBJ)); ?>
      <?php } ?>
      <?php if ($extraFee) { ?>
        <li class="list-group-item bg-light fw-bold">
          Extra Fees
        </li>
        <?php do {

          $amount = $extraFee->fee;
          if ($extraFee->type == 'Refund') $amount = 0 - $amount;

        ?>
          <li class="list-group-item">
            <div class="row">
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" name="<?= htmlspecialchars("extra-fee-" . $extraFee->extraId . "-" . $extraFee->memberId) ?>" id="<?= htmlspecialchars("extra-fee-" . $extraFee->extraId . "-" . $extraFee->memberId) ?>" data-amount="<?= htmlspecialchars(MoneyHelpers::decimalToInt($amount)) ?>">
                  <label class="form-check-label fw-bold" for="<?= htmlspecialchars("extra-fee-" . $extraFee->extraId . "-" . $extraFee->memberId) ?>">
                    <div><?= htmlspecialchars($extraFee->extra) ?> (<?= htmlspecialchars(\SCDS\Formatting\Names::format($extraFee->forename, $extraFee->surname)) ?>)</div>
                    <div><?= htmlspecialchars(MoneyHelpers::formatCurrency((float) $amount, 'GBP')) ?></div>
                  </label>
                </div>
              </div>
              <div class="col-auto text-end">
                <?= htmlspecialchars((new DateTime('first day of this month', new DateTimeZone('Europe/London')))->format('d/m/Y')) ?>
              </div>
            </div>
          </li>
        <?php } while ($extraFee = $getExtraMetadata->fetch(PDO::FETCH_OBJ)); ?>
      <?php } ?>
      <?php if ($item) { ?>
        <li class="list-group-item bg-light fw-bold">
          Other Fees
        </li>
        <?php do {

          $amount = $item->amount;
          if ($item->type == 'Refund') $amount = 0 - $amount;

        ?>
          <li class="list-group-item">
            <div class="row">
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" name="<?= htmlspecialchars("invoice-item-" . $item->id) ?>" id="<?= htmlspecialchars("invoice-item-" . $item->id) ?>" data-amount="<?= htmlspecialchars($amount) ?>">
                  <label class="form-check-label fw-bold" for="<?= htmlspecialchars("invoice-item-" . $item->id) ?>">
                    <div><?= htmlspecialchars($item->name) ?></div>
                    <div><?= htmlspecialchars(MoneyHelpers::formatCurrency((float) MoneyHelpers::intToDecimal($amount), $item->currency)) ?></div>
                  </label>
                </div>
              </div>
              <div class="col-auto text-end">
                <?= htmlspecialchars((new DateTime($item->date, new DateTimeZone('Europe/London')))->format('d/m/Y')) ?>
              </div>
            </div>
          </li>
        <?php } while ($item = $getPending->fetch(PDO::FETCH_OBJ)); ?>
      <?php } ?>
      <li class="list-group-item bg-light fw-bold">
        Total &pound;<span id="early-total" data-total="0">0.00</span>
      </li>
    </ul>

    <div class="alert alert-danger" id="too-low-warning">
      <p class="mb-0">
        <strong>The total to charge is less than the minimum Direct Debit payment amount</strong>
      </p>
      <p class="mb-0">
        Please ensure the amount is greater than &pound;1.
      </p>
    </div>

    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="1" id="confirm-charge" required disabled>
      <label class="form-check-label" for="confirm-charge">
        I have checked the amount I am charging <?= htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) ?> and confirm I want to proceed.
      </label>
    </div>

  </form>

<?php } else if (!$monthExists) { ?>

  <div class="alert alert-warning">
    <p class="mb-0">
      <strong>Awaiting fee calculation</strong>
    </p>
    <p>
      The payment batch for this month has not yet been calculated. We can't force an early payment run before fees have been calculated as it could cause data inconsistencies.
    </p>

    <p class="mb-0">
      Please return to this page in a few hours time. If the issue persists, there may be an issue preventing the batch from running. In this case, please contact SCDS support.
    </p>
  </div>

<?php } else { ?>

  <div class="alert alert-warning">
    <p class="mb-0">
      <strong>There are no pending payments for <?= htmlspecialchars($info['Forename']) ?></strong>
    </p>
    <p class="mb-0">
      Please <a class="alert-link" href="<?= htmlspecialchars(autoUrl("payments/invoice-payments/new")) ?>">add invoice payment items</a>.
    </p>
  </div>

<?php } ?>