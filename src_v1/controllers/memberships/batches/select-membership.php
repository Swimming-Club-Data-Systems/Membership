<?php

if (!isset($_POST['id']) || !isset($_POST['member'])) halt(404);

$id = $_POST['id'];

$user = app()->user;
$db = app()->db;
$tenant = app()->tenant;

$getBatch = $db->prepare("SELECT membershipBatch.ID id, membershipBatch.Completed completed, DueDate due, Total total, PaymentTypes payMethods, PaymentDetails payDetails, membershipBatch.User `user` FROM membershipBatch INNER JOIN users ON users.UserID = membershipBatch.User WHERE membershipBatch.ID = ? AND users.Tenant = ?");
$getBatch->execute([
  $id,
  app()->tenant->getId(),
]);

$batch = $getBatch->fetch(PDO::FETCH_OBJ);

if (!$batch) halt(404);

if (!$user->hasPermission('Admin')) halt(404);

// Get members for this user
$getMembers = $db->prepare("SELECT MForename, MSurname, MemberID FROM members WHERE UserID = ? AND MemberID = ? AND Active ORDER BY MForename ASC, MSurname ASC;");
$getMembers->execute([
  $batch->user,
  $_POST['member']
]);

$member = $getMembers->fetch(PDO::FETCH_OBJ);

// Validate year
$getYears = $db->prepare("SELECT ID FROM `membershipYear` WHERE `Tenant` = ? AND `ID` = ?");
$getYears->execute([
  $tenant->getId(),
  $_POST['membership-year'],
]);
$year = $getYears->fetchColumn();
if (!$year) throw new Exception('Invalid membership year');

// Work out available memberships
$getMemberships = $db->prepare("SELECT `ID` `id`, `Name` `name`, `Description` `description`, `Fees` `fees`, `Type` `type` FROM `clubMembershipClasses` WHERE `Tenant` = ? AND `ID` NOT IN (SELECT `Membership` AS `ID` FROM `memberships` WHERE `Member` = ? AND `Year` = ?) AND `ID` NOT IN (SELECT `Membership` AS `ID` FROM `membershipBatchItems` INNER JOIN membershipBatch ON membershipBatchItems.Batch = membershipBatch.ID WHERE `Member` = ? AND `membershipBatch`.`ID` = ?)");
$getMemberships->execute([
  $tenant->getId(),
  $_POST['member'],
  $year,
  $_POST['member'],
  $batch->id,
]);
$membership = $getMemberships->fetch(PDO::FETCH_OBJ);

ob_clean();
ob_start();

?>

<?php if ($membership) { ?>

  <div class="pt-3">
    <label class="form-label" for="membership">Select membership</label>
    <select class="form-select" id="membership" name="membership" required>
      <option disabled selected value="null">Select a membership</option>
      <?php do { ?>
        <?php
        $fee = 0;
        $feeData = null;
        $discountMessage = "";
        try {
          $feeData = json_decode($membership->fees);
        } catch (Exception $e) {
          // Ignore
        }
        if ($feeData) {
          // reportError($feeData);
          if (isset($feeData->type) && $feeData->type == "PerPerson" && isset($feeData->fees[0])) {
            $fee = MoneyHelpers::intToDecimal($feeData->fees[0]);

            // Current month
            $date = new DateTime('now', new DateTimeZone('Europe/London'));
            $month = (int) $date->format('n') - 1;

            // Ignore discounts if set on both as undefined behaviour
            if (!(isset($feeData->discounts->value) && $feeData->discounts->value[$month] && isset($feeData->discounts->percent) && $feeData->discounts->percent[$month])) {
              // Reduce if discount applies
              if (isset($feeData->discounts->value) && $feeData->discounts->value[$month]) {
                $fee = MoneyHelpers::intToDecimal($feeData->fees[0] - $feeData->discounts->value[$month]);
                $discountMessage = "<p class=\"mb-0\">We have automatically applied a discount of <strong>" . htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($feeData->discounts->value[$month]), 'GBP')) . "</strong> to this membership. The original value was " . htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($feeData->fees[0]), 'GBP')) . ".</p>";
              }

              if (isset($feeData->discounts->percent) && $feeData->discounts->percent[$month]) {
                $originalFee = Brick\Math\BigDecimal::of(MoneyHelpers::intToDecimal($feeData->fees[0]));

                $discountPercentage = Brick\Math\BigDecimal::of($feeData->discounts->percent[$month])->dividedBy(Brick\Math\BigDecimal::of(100));
                $discountAmount = $originalFee->multipliedBy($discountPercentage)->toScale(2, Brick\Math\RoundingMode::HALF_UP);

                $discountAmount = $originalFee->multipliedBy($discountPercentage)->toScale(2, Brick\Math\RoundingMode::HALF_UP);
                $fee = $originalFee->minus($discountAmount);
                $discountMessage = "<p class=\"mb-0\">We have automatically applied a <strong>" . htmlspecialchars(number_format($feeData->discounts->percent[$month], 2)) . "%</strong> discount of <strong>" . htmlspecialchars(MoneyHelpers::formatCurrency($discountAmount, 'GBP')) . "</strong> to this membership. The original value was " . htmlspecialchars(MoneyHelpers::formatCurrency($originalFee, 'GBP')) . ".</p>";
              }
            }
          } else if (isset($feeData->type) && $feeData->type == "NSwimmers" && isset($feeData->fees[0])) {
            $discountMessage = "<p class=\"mb-0\"><strong>Help with fees</strong></p><p>The following standard and discounted (by month) fees apply for this fee;</p>";

            if (!(isset($feeData->discounts->value) && $feeData->discounts->value[$month] && isset($feeData->discounts->percent) && $feeData->discounts->percent[$month])) {
              $discountMessage .= "<table class=\"table\"><thead class=\"table-info\"><tr><th>Number of members</th><th>Normal Price</th><th>Discount</th><th>Discounted Total</th></tr></thead><tbody>";

              // Reduce if discount applies
              if (isset($feeData->discounts->value) && $feeData->discounts->value[$month]) {
                for ($i = 0; $i < sizeof($feeData->fees); $i++) {
                  $thisFee = Brick\Math\BigDecimal::of(MoneyHelpers::intToDecimal($feeData->fees[$i]));
                  $perSwimmerDiscount = Brick\Math\BigDecimal::of(MoneyHelpers::intToDecimal($feeData->discounts->value[$month]));
                  // $discount = MoneyHelpers::formatCurrency($perSwimmerDiscount, 'GBP');
                  $totalDiscount = $perSwimmerDiscount->multipliedBy(Brick\Math\BigDecimal::of($i + 1));
                  $discount = MoneyHelpers::formatCurrency($totalDiscount, 'GBP');
                  $discountedTotal = $thisFee->minus($totalDiscount);
                  $discountAmount = MoneyHelpers::formatCurrency($discountedTotal, 'GBP');

                  $discountMessage .= "<tr><td>" . htmlspecialchars($i + 1) . "</td><td>" . htmlspecialchars(MoneyHelpers::formatCurrency($thisFee, 'GBP')) . "</td><td>" . htmlspecialchars($discount) . "</td><td>" . htmlspecialchars($discountAmount) . "</td></tr>";
                }
              }

              if (isset($feeData->discounts->percent) && $feeData->discounts->percent[$month]) {
                for ($i = 0; $i < sizeof($feeData->fees); $i++) {
                  $thisFee = Brick\Math\BigDecimal::of(MoneyHelpers::intToDecimal($feeData->fees[$i]));

                  $discountPercentage = Brick\Math\BigDecimal::of($feeData->discounts->percent[$month])->dividedBy(Brick\Math\BigDecimal::of(100));
                  $perSwimmerDiscount = $thisFee->multipliedBy($discountPercentage)->toScale(2, Brick\Math\RoundingMode::HALF_UP);

                  $totalDiscount = $perSwimmerDiscount->multipliedBy(Brick\Math\BigDecimal::of($i + 1));
                  $discount = MoneyHelpers::formatCurrency($totalDiscount, 'GBP');
                  $discountedTotal = $thisFee->minus($totalDiscount);
                  $discountAmount = MoneyHelpers::formatCurrency($discountedTotal, 'GBP');

                  $discountMessage .= "<tr><td>" . htmlspecialchars($i + 1) . "</td><td>" . htmlspecialchars(MoneyHelpers::formatCurrency($thisFee, 'GBP')) . "</td><td>" . htmlspecialchars(number_format($feeData->discounts->percent[$month], 2) . "% (" . $discount) . ")</td><td>" . htmlspecialchars($discountAmount) . "</td></tr>";
                }
              }

              $discountMessage .= "</tbody></table>";
            }

            $discountMessage .= "<p>As the value of this fee varies according to the number of members in a family, you only need to charge the difference between the amount already paid for this membership and the total which should be paid for it.</p>";

            // Calculate paid total
            $getTotal = $db->prepare("SELECT COUNT(`Amount`) AS `count`, SUM(`Amount`) AS `total` FROM memberships INNER JOIN members ON members.MemberID = memberships.Member WHERE members.UserID = ? AND memberships.Year = ?");
            $getTotal->execute([
              $batch->user,
              $year,
            ]);
            $total = $getTotal->fetch(PDO::FETCH_OBJ);

            if ($total) {
              $discountMessage .= "<p class=\"mb-0\"><strong>" . htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal((int) $total->total), 'GBP')) . "</strong> has already been paid for " . htmlspecialchars($total->count) . " members on the user's account with this membership class.</p>";
            } else {
              $discountMessage .= "<p class=\"mb-0\">Total already paid information is currently unavailable.</p>";
            }
          }
        }
        ?>
        <option value="<?= htmlspecialchars($membership->id) ?>" data-id="<?= htmlspecialchars($membership->id) ?>" data-name="<?= htmlspecialchars($membership->name) ?>" data-description="<?= htmlspecialchars($membership->description) ?>" data-fee="<?= htmlspecialchars($fee) ?>" data-fees="<?= htmlspecialchars($membership->fees) ?>" data-type="<?= htmlspecialchars($membership->type) ?>" data-discount-message="<?= htmlspecialchars($discountMessage) ?>"><?= htmlspecialchars($membership->name) ?></option>
      <?php } while ($membership = $getMemberships->fetch(PDO::FETCH_OBJ)); ?>
    </select>
  </div>

  <div id="add-membership-form-details-opts" class="collapse">

    <div id="membership-info-box"></div>

    <div class="pt-3">
      <div class="mb-3">
        <label for="membership-amount" class="form-label">Fee</label>
        <div class="input-group mb-3">
          <span class="input-group-text">&pound;</span>
          <input type="num" class="form-control" id="membership-amount" name="membership-amount" min="0" step="0.01" placeholder="0" value="0.00" required>
        </div>
      </div>

      <div class="">
        <label for="membership-notes" class="form-label">Notes <span class="text-muted">(optional)</span></label>
        <textarea class="form-control" id="membership-notes" name="membership-notes" rows="3"></textarea>
        <div class="small">Place explanatory notes here.</div>
      </div>
    </div>
  </div>

<?php } else { ?>

  <div class="alert alert-warning">
    <p class="mb-0">
      <strong>
        There are no additional memberships available to assign to <?= htmlspecialchars($member->MForename . ' ' . $member->MSurname) ?>
      </strong>
    </p>
    <p class="mb-0">
      This is because they already hold all available memberships for the current membership year, or available memberships have already been assigned to a batch for the <?= htmlspecialchars($batch->yearName) ?> membership year.
    </p>
  </div>

<?php } ?>

<?php

$html = ob_get_clean();

// reportError(htmlentities($html));

header('content-type: application/json');
echo json_encode([
  'html' => $html
]);
