<?php

use function GuzzleHttp\json_decode;

$db = app()->db;

$db->beginTransaction();

$getSubscription = $db->prepare("SELECT tenantPaymentSubscriptions.ID, PaymentMethod, Memo, Footer, StartDate, EndDate, Active, tenants.Name, tenants.ID Tenant FROM tenantPaymentSubscriptions INNER JOIN tenantStripeCustomers ON tenantStripeCustomers.CustomerID = tenantPaymentSubscriptions.Customer INNER JOIN tenants ON tenants.ID = tenantStripeCustomers.Tenant WHERE tenantPaymentSubscriptions.ID = ?");
$getSubscription->execute([
  $id
]);
$subscription = $getSubscription->fetch(PDO::FETCH_ASSOC);

if (!$subscription) halt(404);

try {

  if (!\SCDS\CSRF::verify()) throw new Exception('CSRF validity error');

  $memo = $footer = null;
  if (isset($_POST['subscription-invoice-memo']) && mb_strlen(trim($_POST['subscription-invoice-memo']))) {
    $memo = trim($_POST['subscription-invoice-memo']);
  }

  if (isset($_POST['subscription-invoice-footer']) && mb_strlen(trim($_POST['subscription-invoice-footer']))) {
    $footer = trim($_POST['subscription-invoice-footer']);
  }

  $addSubscriptionPlans = $db->prepare("INSERT INTO tenantPaymentSubscriptionProducts (ID, Subscription, Plan, Quantity, NextBills, Discount, DiscountType, TaxRate) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");

  $updateSubscriptionPlans = $db->prepare("UPDATE tenantPaymentSubscriptionProducts SET Quantity = ? WHERE Plan = ? AND Subscription = ?;");

  $deleteSubscriptionPlans = $db->prepare("DELETE FROM tenantPaymentSubscriptionProducts WHERE Plan = ? AND Subscription = ?;");

  $json = json_decode($_POST['subscription-plans-object'], true);

  $check = $db->prepare("SELECT COUNT(*) FROM tenantPaymentPlans WHERE ID = ?");
  $checkPlan = $db->prepare("SELECT COUNT(*) FROM tenantPaymentSubscriptionProducts WHERE Plan = ? AND Subscription = ?");

  foreach ($json['products'] as $product) {
    foreach ($product['plans'] as $plan) {

      // Check plan exists
      $check->execute([
        $plan['id']
      ]);

      // See if sub plan exists
      $checkPlan->execute([
        $plan['id'],
        $id,
      ]);

      if ($check->fetchColumn() > 0 && (int) $plan['quantity'] > 0) {
        if ($checkPlan->fetchColumn() > 0) {
          $updateSubscriptionPlans->execute([
            (int) $plan['quantity'],
            $plan['id'],
            $id,
          ]);
        } else {
          $addSubscriptionPlans->execute([
            Ramsey\Uuid\Uuid::uuid4()->toString(),
            $id,
            $plan['id'],
            (int) $plan['quantity'],
            (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y-m-d'),
            null,
            null,
            null,
          ]);
        }
      } else if ((int) $plan['quantity'] == 0) {
        // Delete entry
        $deleteSubscriptionPlans->execute([
          $plan['id'],
          $id,
        ]);
      }
    }
  }

  // Loop over to check for removed plans
  $getPlans = $db->prepare("SELECT Plan, Product FROM tenantPaymentSubscriptionProducts INNER JOIN tenantPaymentPlans ON tenantPaymentPlans.ID = tenantPaymentSubscriptionProducts.Plan WHERE Subscription = ?");
  $getPlans->execute([
    $id,
  ]);
  while ($plan = $getPlans->fetch(PDO::FETCH_ASSOC)) {
    if (!isset($json['products'][$plan['Product']]['plans'][$plan['Plan']])) {
      // Delete plan
      $deleteSubscriptionPlans->execute([
        $plan['Plan'],
        $id,
      ]);
    }
  }

  // Check count of plan items
  $check = $db->prepare("SELECT COUNT(*) FROM tenantPaymentSubscriptionProducts WHERE Subscription = ?");
  $check->execute([
    $id,
  ]);

  if ($check->fetchColumn() == 0) throw new Exception('No items on the plan');

  $db->commit();

  $_SESSION['SubscriptionEditSuccess'] = true;
} catch (Exception $e) {

  $db->rollBack();

  $_SESSION['SubscriptionEditError'] = $e->getMessage();
}

http_response_code(302);
header("location: " . autoUrl("admin/payments/subscriptions/$id"));
