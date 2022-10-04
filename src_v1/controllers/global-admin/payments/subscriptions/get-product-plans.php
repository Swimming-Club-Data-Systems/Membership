<?php

$db = app()->db;

use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

try {

  $getProducts = $db->prepare("SELECT `ID`, `Name`, `Description`, `Updated` FROM `tenantPaymentProducts` WHERE `ID` = ?;");
  $getProducts->execute([
    $_POST['product'],
  ]);
  $product = $getProducts->fetch(PDO::FETCH_ASSOC);

  if (!$product) throw new Exception('No product');

  $getPlans = $db->prepare("SELECT `ID`, `PricePerUnit`, `UsageType`, `Currency`, `BillingInterval`, `Name`, `Updated` FROM `tenantPaymentPlans` WHERE `Product` = ? ORDER BY `PricePerUnit` ASC, `Name` ASC");
  $getPlans->execute([
    $_POST['product'],
  ]);
  $plan = $getPlans->fetch(PDO::FETCH_ASSOC);

  $formatter = new NumberFormatter(app()->locale, NumberFormatter::CURRENCY);

  ob_start();

?>

  <?php if ($plan) { ?>
    <option value="" selected disabled>Select a plan</option>
    <?php

    do { ?>
      <option id="<?= htmlspecialchars('plan-select-' . $plan['ID']) ?>" value="<?= htmlspecialchars($plan['ID']) ?>" data-name="<?= htmlspecialchars($plan['Name']) ?>" data-amount="<?= htmlspecialchars($plan['PricePerUnit']) ?>" data-currency="<?= htmlspecialchars($plan['Currency']) ?>"><?= htmlspecialchars($plan['Name'] . ' (' . $formatter->formatCurrency((string) (\Brick\Math\BigDecimal::of((string) $plan['PricePerUnit']))->withPointMovedLeft(2)->toScale(2), $plan['Currency']) . ')') ?></option>
    <?php } while ($plan = $getPlans->fetch(PDO::FETCH_ASSOC)); ?>
  <?php } else { ?>
    <option value="" disabled selected>No plans available for this product</option>
  <?php } ?>

<?php

  $html = ob_get_clean();

  header('content-type: application/json');
  echo json_encode([
    'success' => true,
    'html' => trim($html),
  ]);
} catch (Exception $e) {

  $message = $e->getMessage();
  if (get_class($e) == 'PDOException') {
    $message = 'A database error occurred';
  }

  header('content-type: application/json');
  echo json_encode([
    'success' => false,
    'error' => $message,
  ]);
}
