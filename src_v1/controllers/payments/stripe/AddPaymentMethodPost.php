<?php

$db = app()->db;
$tenant = app()->tenant;

// See your keys here: https://dashboard.stripe.com/account/apikeys
\Stripe\Stripe::setApiKey(getenv('STRIPE'));

$setupIntent = null;
if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['StripeSetupIntentId'])) {
  halt(404);
} else {
  try {
    $setupIntent = \Stripe\SetupIntent::retrieve(
      $_SESSION['TENANT-' . app()->tenant->getId()]['StripeSetupIntentId'],
      [
        'stripe_account' => $tenant->getStripeAccount()
      ]
    );
  } catch (Exception $e) {
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['StripeSetupIntentId']);
    header("Location: " . autoUrl("payments/cards/add"));
    return;
  }
}

$getUserEmail = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile FROM users WHERE UserID = ?");
$getUserEmail->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
$user = $getUserEmail->fetch(PDO::FETCH_ASSOC);

$checkIfCustomer = $db->prepare("SELECT COUNT(*) FROM stripeCustomers WHERE User = ?");
$checkIfCustomer->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);

$customer = null;
if ($checkIfCustomer->fetchColumn() == 0) {
  // Create a Customer:
  $customer = \Stripe\Customer::create([
    "name" => $user['Forename'] . ' ' . $user['Surname'],
    "description" => "Customer for " . $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'] . ' (' . $user['EmailAddress'] . ')',
    'email' => $user['EmailAddress'],
    'phone' => $user['Mobile']
  ], [
    'stripe_account' => $tenant->getStripeAccount()
  ]);

  // YOUR CODE: Save the customer ID and other info in a database for later.
  $id = $customer->id;
  $addCustomer = $db->prepare("INSERT INTO stripeCustomers (User, CustomerID) VALUES (?, ?)");
  $addCustomer->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
    $id
  ]);
} else {
  $getCustID = $db->prepare("SELECT CustomerID FROM stripeCustomers WHERE User = ?");
  $getCustID->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
  $customer = \Stripe\Customer::retrieve(
    $getCustID->fetchColumn(),
    [
      'stripe_account' => $tenant->getStripeAccount()
    ]
  );

  // Check whether we should update user details
  if ($customer->name != $user['Forename'] . ' ' . $user['Surname'] || $customer->email != $user['EmailAddress'] || $customer->phone != $user['Mobile']) {
    // Some details are not the same so let's update the stripe customer
    $customer = \Stripe\Customer::update(
      $customer->id,
      [
        "name" => $user['Forename'] . ' ' . $user['Surname'],
        'email' => $user['EmailAddress'],
        'phone' => $user['Mobile']
      ],
      [
        'stripe_account' => $tenant->getStripeAccount()
      ]
    );

  }
}

try {
  // Attach payment method to the customer:
  \Stripe\Customer::update(
    $customer->id,
    [
      'email' => $user['EmailAddress'],
      'phone' => $user['Mobile']
    ],
    [
      'stripe_account' => $tenant->getStripeAccount()
    ]
  );


  $pm = \Stripe\PaymentMethod::retrieve(
    $setupIntent->payment_method,
    [
      'stripe_account' => $tenant->getStripeAccount()
    ]
  );
  $pm->attach(['customer' => $customer->id]);

  $name = 'Payment Card';

  // Get the payment method details
  $id = $pm->id;
  $nameOnCard = $pm->card->name;
  $city = $pm->billing_details->address->city;
  $country = $pm->billing_details->address->country;
  $line1 = $pm->billing_details->address->line1;
  $line2 = $pm->billing_details->address->line2;
  $postal_code = $pm->billing_details->address->postal_code;
  $brand = $pm->card->brand;
  $issueCountry = $pm->card->country;
  $expMonth = $pm->card->exp_month;
  $expYear = $pm->card->exp_year;
  $funding = $pm->card->funding;
  $last4 = $pm->card->last4;
  $threeDSecure = $pm->card->three_d_secure_usage->supported;

  $getCardCount = $db->prepare("SELECT COUNT(*) FROM stripePayMethods WHERE Customer = ? AND Fingerprint = ? AND Reusable = ?");
  $getCardCount->execute([
    $customer->id,
    $pm->card->fingerprint,
    1
  ]);

  if ($getCardCount->fetchColumn() > 0) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['PayCardError'] = true;
    $_SESSION['TENANT-' . app()->tenant->getId()]['PayCardErrorMessage'] = 'This card is already connected to your account';
    header("Location: " . autoUrl("payments/cards/add"));
    return;
  }

  $addPaymentDetails = $db->prepare("INSERT INTO stripePayMethods (Customer, MethodID, `Name`, CardName, City, Country, Line1, Line2, PostCode, Brand, IssueCountry, ExpMonth, ExpYear, Funding, Last4, Fingerprint, Reusable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $addPaymentDetails->execute([
    $customer->id,
    $id,
    $name,
    $nameOnCard,
    $city,
    $country,
    $line1,
    $line2,
    $postal_code,
    $brand,
    $issueCountry,
    $expMonth,
    $expYear,
    $funding,
    $last4,
    $pm->card->fingerprint,
    true,
  ]);

  unset($_SESSION['TENANT-' . app()->tenant->getId()]['StripeSetupIntentId']);
  $_SESSION['TENANT-' . app()->tenant->getId()]['PayCardSetupSuccess'] = true;
  $_SESSION['TENANT-' . app()->tenant->getId()]['PayCardSetupSuccessBrand'] = getCardBrand($brand);
  header("Location: " . autoUrl("payments/cards"));
} catch (Exception $e) {
  $body = $e->getJsonBody();
  $err  = $body['error']['message'];
  $_SESSION['TENANT-' . app()->tenant->getId()]['PayCardError'] = true;
  $_SESSION['TENANT-' . app()->tenant->getId()]['PayCardErrorMessage'] = $err;
  header("Location: " . autoUrl("payments/cards/add"));
}