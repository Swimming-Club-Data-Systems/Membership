<?php

use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

$db = app()->db;

\Stripe\Stripe::setApiKey(getenv('STRIPE'));
$stripe = new \Stripe\StripeClient(getenv('STRIPE'));

$db->beginTransaction();

try {

  if (!\SCDS\CSRF::verify()) throw new Exception('CSRF validity error');

  // UPDATING
  $tenant = Tenant::fromId($_POST['subscription-tenant']);
  $customer = $tenant->getStripeCustomer();

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

  $paymentMethod = null;
  if ($_POST['bills-when'] == 'immediately') {
    // Check payment method
    $getPm = $db->prepare("SELECT COUNT(*) FROM tenantPaymentMethods WHERE MethodID = ? AND Customer = ? AND Usable");
    $getPm->execute([
      $_POST['subscription-payment-method'],
      $customer,
    ]);
    if ($getPm->fetchColumn() == 0) throw new Exception('No payment method');
    $paymentMethod = $_POST['subscription-payment-method'];
  }

  $date = new DateTime($_POST['invoice-date'], new DateTimeZone('Europe/London'));

  $id = Ramsey\Uuid\Uuid::uuid4()->toString();

  $lineItems = [];

  $json = json_decode($_POST['subscription-plans-object'], true);

  $getDetails = $db->prepare("SELECT tenantPaymentProducts.ID Product, tenantPaymentProducts.Name ProductName, tenantPaymentProducts.Description ProductDescription, tenantPaymentPlans.ID Plan, tenantPaymentPlans.Name PlanName, tenantPaymentPlans.PricePerUnit, tenantPaymentPlans.Currency FROM tenantPaymentPlans INNER JOIN tenantPaymentProducts ON tenantPaymentProducts.ID = tenantPaymentPlans.Product WHERE tenantPaymentPlans.ID = ?");

  $currency = null;
  $amount = 0;

  foreach ($json['products'] as $product) {
    foreach ($product['plans'] as $plan) {

      // Check plan exists
      $getDetails->execute([
        $plan['id']
      ]);

      $item = $getDetails->fetch(PDO::FETCH_ASSOC);

      if ($item && (int) $plan['quantity'] > 0) {
        $toPay = $plan['quantity'] * $item['PricePerUnit'];
        $amount += $toPay;
        $lineItems[] = [
          Ramsey\Uuid\Uuid::uuid4()->toString(),
          $id,
          json_encode([
            'product' => [
              'id' => $item['Product'],
              'name' => $item['ProductName']
            ],
            'plan_description' => [
              'id' => $item['Plan'],
              'name' => $item['PlanName']
            ],
          ]),
          $toPay,
          $item['Currency'],
          'debit',
          (int) $plan['quantity'],
          $item['PricePerUnit'],
          0,
          0,
        ];

        if ($currency && $item['Currency'] != $currency) {
          throw new Exception('Currency mismatch');
        } else if (!$currency) {
          $currency = $item['Currency'];
        }
      }
    }
  }

  if (sizeof($lineItems) == 0) throw new Exception('No payment items');

  // Create the payment intent
  $intent = $stripe->paymentIntents->create([
    'amount' => $amount,
    'currency' => $currency,
    'payment_method_types' => ['bacs_debit', 'card'],
    'customer' => $customer,
  ]);

  if ($_POST['bills-when'] == 'immediately') {
    $intent = $stripe->paymentIntents->confirm(
      $intent->id,
      [
        'payment_method' => $paymentMethod,
        'off_session' => true,
      ]
    );
  }

  // Add payment intent
  $addIntent = $db->prepare("INSERT INTO `tenantPaymentIntents` (`ID`, `IntentID`, `PaymentMethod`, `Amount`, `Currency`, `Status`) VALUES (?, ?, ?, ?, ?, ?)");
  $addIntent->execute([
    Ramsey\Uuid\Uuid::uuid4()->toString(),
    $intent->id,
    $intent->payment_method,
    $intent->amount,
    $intent->currency,
    $intent->status,
  ]);

  // Create invoice
  $addInvoice = $db->prepare("INSERT INTO `tenantPaymentInvoices` (`ID`, `Reference`, `Customer`, `PaymentIntent`, `Date`, `SupplyDate`, `Company`, `Currency`, `PaymentTerms`, `HowToPay`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $addInvoice->execute([
    $id,
    'SCDS-ONLINE-x',
    $customer,
    $intent->id,
    $date->format('Y-m-d'),
    $date->format('Y-m-d'),
    json_encode([
      'company_number' => null,
      'company_vat_number' => null,
      'company_address' => null,
    ]),
    $intent->currency,
    'Pay within 30 days of invoice date',
    'Pay electronically via the SCDS Online Payments Service',
  ]);

  // Add invoice line items
  $addLineItem = $db->prepare("INSERT INTO `tenantPaymentInvoiceItems` (`ID`, `Invoice`, `Description`, `Amount`, `Currency`, `Type`, `Quantity`, `PricePerUnit`, `VATAmount`, `VATRate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  foreach ($lineItems as $item) {
    $addLineItem->execute($item);
  }

  $db->commit();

  $_SESSION['InvoiceAddSuccess'] = true;
  http_response_code(302);
  header("location: " . autoUrl("admin/payments/invoices/$id"));
} catch (Exception $e) {

  $db->rollBack();

  $_SESSION['InvoiceAddError'] = $e->getMessage();
  http_response_code(302);
  header("location: " . autoUrl('admin/payments/invoices/new'));
}
