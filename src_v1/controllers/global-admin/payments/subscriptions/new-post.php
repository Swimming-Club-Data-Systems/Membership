<?php

use function GuzzleHttp\json_decode;

$db = app()->db;

$db->beginTransaction();

try {

  if (!\SCDS\CSRF::verify()) throw new Exception('CSRF validity error');

  $getTenant = $db->prepare("SELECT `CustomerID` FROM `tenantStripeCustomers` WHERE `Tenant` = ?");
  $getTenant->execute([
    $_POST['subscription-tenant'],
  ]);
  $customer = $getTenant->fetchColumn();

  if (!$customer) throw new Exception('No customer');

  $memo = $footer = null;
  if (isset($_POST['subscription-invoice-memo']) && mb_strlen(trim($_POST['subscription-invoice-memo']))) {
    $memo = trim($_POST['subscription-invoice-memo']);
  }

  if (isset($_POST['subscription-invoice-footer']) && mb_strlen(trim($_POST['subscription-invoice-footer']))) {
    $footer = trim($_POST['subscription-invoice-footer']);
  }

  $date = new DateTime('now', new DateTimeZone('Europe/London'));
  switch ($_POST['subscription-first-bills']) {
    case 'immediately':
      $date = new DateTime('now', new DateTimeZone('Europe/London'));
      break;
    case 'next-first':
      $date = new DateTime('first day of next month', new DateTimeZone('Europe/London'));
      break;
    case 'custom':
      $date = new DateTime($_POST['subscription-first-bills-date'], new DateTimeZone('Europe/London'));
      break;
  }

  // Check payment method
  $getPm = $db->prepare("SELECT COUNT(*) FROM tenantPaymentMethods WHERE MethodID = ? AND Customer = ? AND Usable");
  $getPm->execute([
    $_POST['subscription-payment-method'],
    $customer,
  ]);
  if ($getPm->fetchColumn() == 0) throw new Exception('No payment method');

  $id = Ramsey\Uuid\Uuid::uuid4()->toString();
  $addSubscription = $db->prepare("INSERT INTO tenantPaymentSubscriptions (ID, Customer, PaymentMethod, Memo, Footer, StartDate, EndDate, Active) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");
  $addSubscription->execute([
    $id,
    $customer,
    $_POST['subscription-payment-method'],
    $memo,
    $footer,
    $date->format('Y-m-d'),
    null,
    (int) true,
  ]);

  $addSubscriptionPlans = $db->prepare("INSERT INTO tenantPaymentSubscriptionProducts (ID, Subscription, Plan, Quantity, NextBills, Discount, DiscountType, TaxRate) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");

  $json = json_decode($_POST['subscription-plans-object'], true);

  $check = $db->prepare("SELECT COUNT(*) FROM tenantPaymentPlans WHERE ID = ?");

  foreach ($json['products'] as $product) {
    foreach ($product['plans'] as $plan) {

      // Check plan exists
      $check->execute([
        $plan['id']
      ]);

      if ($check->fetchColumn() > 0 && (int) $plan['quantity'] > 0) {
        $addSubscriptionPlans->execute([
          Ramsey\Uuid\Uuid::uuid4()->toString(),
          $id,
          $plan['id'],
          (int) $plan['quantity'],
          $date->format('Y-m-d'),
          null,
          null,
          null,
        ]);
      }
    }
  }

  // Check count of plan items
  $check = $db->prepare("SELECT COUNT(*) FROM tenantPaymentSubscriptionProducts WHERE Subscription = ?");
  $check->execute([
    $id,
  ]);
  
  if ($check->fetchColumn() == 0) throw new Exception('No items on the plan');

  $db->commit();

  $_SESSION['SubscriptionAddSuccess'] = true;
  http_response_code(302);
  header("location: " . autoUrl("admin/payments/subscriptions/$id"));
} catch (Exception $e) {

  $db->rollBack();

  $_SESSION['SubscriptionAddError'] = $e->getMessage();
  http_response_code(302);
  header("location: " . autoUrl('admin/payments/subscriptions/new'));
}
