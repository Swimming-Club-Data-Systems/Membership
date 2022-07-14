<?php

if (!isset($_GET['tenant']) || !isset($_GET['tenant'])) halt(404);

if (!\SCDS\CSRF::verify()) {
  halt(403);
}

$db = app()->db;
$tenant = Tenant::fromUUID($_GET['tenant']);

if ($at = $tenant->getStripeAccount()) {
  // Already got it, halt
  halt(404);
}

// Get User
$getUser = $db->prepare("SELECT `Forename`, `Surname`, `Password` FROM `users` WHERE `UserID` = ? AND `Tenant` = ?");
$getUser->execute([
  $_GET['user'],
  $tenant->getId()
]);
$user = $getUser->fetch(PDO::FETCH_ASSOC);

if (!$user) halt(404);

// Validate password
if (!password_verify($_POST['password'], $user['Password'])) {
  // Invalid
  $_SESSION['STRIPE_INVALID_PASSWORD'] = true;
  http_response_code(302);
  header("Location: " . autoUrl('services/stripe/connect?tenant=' . urlencode($tenant->getUUID()) . '&user=' . urlencode($_GET['user'])));
} else {


  // You should store your client ID and secret in environment variables rather than
  // committing them with your code
  $client = new OAuth2\Client(getenv('STRIPE_CLIENT_ID'), getenv('STRIPE'));

  $url = 'https://connect.stripe.com/oauth/authorize';

  $_SESSION['Stripe-Reg-OAuth'] = [
    'tenant' => $tenant->getId(),
  ];

  $authorizeUrl = $client->getAuthenticationUrl(
    $url,
    webhookUrl("services/stripe/redirect", false), // Your redirect URL
    [
      'response_type' => 'code',
      'scope' => 'read_write',
    ]
  );

  // You'll now want to direct your user to the URL - you could redirect them or display it
  // as a link on the page
  http_response_code(302);
  header("Location: " . $authorizeUrl);
}
