<?php

$id = isset($_GET['id']) ? $_GET['id'] : null;

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();

$checkoutSession = \SCDS\Checkout\Session::retrieve($id);

$items = $checkoutSession->getItems();

$expMonth = date("m");
$expYear = date("Y");

$customer = $db->prepare("SELECT CustomerID FROM stripeCustomers WHERE User = ?");
$customer->execute([$checkoutSession->user]);
$customerId = $customer->fetchColumn();

$numberOfCards = $db->prepare("SELECT COUNT(*) `count`, stripePayMethods.ID FROM stripePayMethods INNER JOIN stripeCustomers ON stripeCustomers.CustomerID = stripePayMethods.Customer WHERE User = ? AND Reusable = ? AND (ExpYear > ? OR (ExpYear = ? AND ExpMonth >= ?))");
$numberOfCards->execute([$checkoutSession->user, 1, $expYear, $expYear, $expMonth]);
$countCards = $numberOfCards->fetch(PDO::FETCH_ASSOC);

$getCards = $db->prepare("SELECT stripePayMethods.ID, `MethodID`, stripePayMethods.Customer, stripePayMethods.Last4, stripePayMethods.Brand FROM stripePayMethods INNER JOIN stripeCustomers ON stripeCustomers.CustomerID = stripePayMethods.Customer WHERE User = ? AND Reusable = ? AND (ExpYear > ? OR (ExpYear = ? AND ExpMonth >= ?)) ORDER BY `Name` ASC");
$getCards->execute([$checkoutSession->user, 1, $expYear, $expYear, $expMonth]);
$cards = $getCards->fetchAll(PDO::FETCH_ASSOC);

$methodId = $customerID = null;

$paymentIntent = $checkoutSession->getPaymentIntent();

$paymentRequestItems = [];
$paymentRequestItems[] = [
  'label' => 'Subtotal',
  'amount' => $paymentIntent->amount
];

$numFormatter = new NumberFormatter("en", NumberFormatter::SPELLOUT);

$markdown = new \ParsedownExtra();
$markdown->setSafeMode(true);

$redirect = $checkoutSession->getUrl();
if (isset($checkoutSession->metadata->return) && $checkoutSession->metadata->return->instant) {
  $redirect = $checkoutSession->metadata->return->url;
  // $checkoutSession->metadata->return->instant?
}

$cancelUrl = autoUrl('');
if (isset($checkoutSession->metadata->cancel)) {
  $cancelUrl = $checkoutSession->metadata->cancel->url;
}

$existingMethods = [];

foreach ($cards as $card) {
  $existingMethods[] = [
    "id" => $card["MethodID"],
    "type" => "card",
    "type_data" => [
      "brand" => $card['Brand'],
      "description" => $card['Last4'],
    ],
  ];
}

echo json_encode([
  "cancel_url" => $cancelUrl,
  "redirect_url" => $redirect,
  "payment_request_items" => $paymentRequestItems,
  "client_secret" => $paymentIntent->client_secret,
  "org_name" => config('CLUB_NAME'),
  "amount" => $paymentIntent->amount,
  "currency" => $paymentIntent->currency,
  "stripe_account_id" => $tenant->getStripeAccount(),
  "payment_methods" => $existingMethods,
  "items" => $items,
  "test_mode" => !$paymentIntent->livemode,
]);
