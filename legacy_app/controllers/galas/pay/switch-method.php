<?php

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();

\Stripe\Stripe::setApiKey(getenv('STRIPE'));

if (!isset($_POST['method'])) {
  header("Location: " . autoUrl("galas/pay-for-entries/checkout"));
  return;
}

if (!isset($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['GalaPaymentIntent'])) {
  halt(404);
}

$toId = '';

if ($_POST['method'] == 'select') {
  $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['AddNewCard'] = true;

  try {
    \Stripe\PaymentIntent::update(
      $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['GalaPaymentIntent'], [
        'payment_method' => null,
      ]
    );
    if (isset($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['GalaPaymentMethodID'])) {
      unset($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['GalaPaymentMethodID']);
    }
  } catch (Exception $e) {
    pre($e);
    halt(500);
  }
} else {
  if (isset($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['AddNewCard'])) {
    unset($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['AddNewCard']);
  }

  $getCards = $db->prepare("SELECT COUNT(*) `count`, MethodID, CustomerID FROM stripePayMethods INNER JOIN stripeCustomers ON stripeCustomers.CustomerID = stripePayMethods.Customer WHERE User = ? AND stripePayMethods.ID = ?");
  $getCards->execute([$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'], $_POST['method']]);

  $details = $getCards->fetch(PDO::FETCH_ASSOC);
  if ($details['count'] > 0) {
    try {
      \Stripe\PaymentIntent::update(
        $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['GalaPaymentIntent'], [
          'payment_method' => $details['MethodID'],
          'customer' => $details['CustomerID'],
        ]
      );
      $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['GalaPaymentMethodID'] = $_POST['method'];
      $toId = '#saved-cards';
    } catch (Exception $e) {
      pre($e);
      halt(500);
    }
  } else {
    halt(404);
  }
}

header("Location: " . autoUrl("galas/pay-for-entries/checkout" . $toId));