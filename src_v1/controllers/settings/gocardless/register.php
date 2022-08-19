<?php

$tenant = app()->tenant;
$user = app()->user;

if ($at = app()->tenant->getGoCardlessAccessToken()) {
  // Already go it, halt
  halt(404);
}

// You should store your client ID and secret in environment variables rather than
// committing them with your code
$client = new OAuth2\Client(getenv('GOCARDLESS_CLIENT_ID'), getenv('GOCARDLESS_CLIENT_SECRET'));

$url = 'https://connect.gocardless.com/oauth/authorize';
if (bool(getenv('IS_DEV'))) {
  $url = 'https://connect-sandbox.gocardless.com/oauth/authorize';
}

$_SESSION['GC-Reg-OAuth'] = [
  'tenant' => $tenant->getId(),
];

$authorizeUrl = $client->getAuthenticationUrl(
  // Once you go live, this should be set to https://connect.gocardless.com. You'll also
  // need to create a live app and update your client ID and secret.
  $url,
  autoUrl("services/gc/redirect", false), // Your redirect URL
  [
    'scope' => 'read_write',
    'initial_view' => 'login',
    'prefill' => [
      'email' => $user->getEmail(),
      'given_name' => $user->getFirstName(),
      'family_name' => $user->getLastName(),
      'organisation_name' => $tenant->getName()
    ]
  ]
);

// You'll now want to direct your user to the URL - you could redirect them or display it
// as a link on the page
header("Location: " . $authorizeUrl);
