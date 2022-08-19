<?php

$db = app()->db;

use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

try {

  $getTenant = $db->prepare("SELECT COUNT(*) FROM `tenants` WHERE `ID` = ?");
  $getTenant->execute([
    $_POST['tenant'],
  ]);
  if ($getTenant->fetchColumn() == 0) halt(404);

  $getPaymentMethods = $db->prepare("SELECT `TypeData`, `MethodID`, `Type`, `AcceptanceData`, `MethodDetails` FROM `tenantPaymentMethods` INNER JOIN `tenantStripeCustomers` ON tenantPaymentMethods.Customer = tenantStripeCustomers.CustomerID LEFT JOIN tenantPaymentMandates ON tenantPaymentMandates.PaymentMethod = tenantPaymentMethods.MethodID WHERE `Usable` AND `Tenant` = ?");
  $getPaymentMethods->execute([
    $_POST['tenant'],
  ]);
  $paymentMethod = $getPaymentMethods->fetch(PDO::FETCH_ASSOC);

  ob_start();

?>

  <?php if ($paymentMethod) { ?>
    <option value="" selected disabled>Select a payment method</option>
    <?php

    do {

      $json = json_decode($paymentMethod['TypeData']);
      $methodDetails = null;
      if ($paymentMethod['MethodDetails']) $methodDetails = json_decode($paymentMethod['MethodDetails']);
      $title = htmlspecialchars('Payment method ' . $paymentMethod['MethodID']);
      switch ($paymentMethod['Type']) {
        case 'card':
          $title = htmlspecialchars(mb_convert_case($json->brand, MB_CASE_TITLE)) . ' ' . htmlspecialchars($json->funding) . ' card ' . htmlspecialchars($json->last4);
          break;
        case 'bacs_debit':
          $title = htmlspecialchars('Direct Debit Ref: ' . ' ' . $methodDetails->bacs_debit->reference);
          break;
      }

    ?>
      <option value="<?= htmlspecialchars($paymentMethod['MethodID']) ?>"><?= $title ?></option>
    <?php } while ($paymentMethod = $getPaymentMethods->fetch(PDO::FETCH_ASSOC)); ?>
  <?php } else { ?>
    <option value="" disabled selected>No payment methods available</option>
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
