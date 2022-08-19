<?php

if (!isset($_SESSION['GC-Reg-OAuth'])) {
  halt(404);
}

$tenant = \Tenant::fromId((int) $_SESSION['GC-Reg-OAuth']['tenant']);

if (!$tenant || !isset($_GET['code'])) {
  halt(404);
}

try {

  $client = new OAuth2\Client(getenv('GOCARDLESS_CLIENT_ID'),
                              getenv('GOCARDLESS_CLIENT_SECRET'));

  $url = 'https://connect.gocardless.com/oauth/access_token';
  if (bool(getenv('IS_DEV'))) {
    $url = 'https://connect-sandbox.gocardless.com/oauth/access_token';
  }

  // You'll need to use exactly the same redirect URI as in the last step
  $response = $client->getAccessToken(
      $url,
      'authorization_code',
      ['code' => $_GET['code'], 'redirect_uri' => autoUrl("services/gc/redirect", false)]
  );

  $tenant->setGoCardlessAccessToken($response['result']['organisation_id'], $response['result']['access_token']);

  $_SESSION['TENANT-' . $tenant->getId()]['GC-Reg-Success'] = true;
  unset($_SESSION['GC-Reg-OAuth']['tenant']);

} catch (Exception $e) {
  $_SESSION['TENANT-' . $tenant->getId()]['GC-Reg-Error'] = true;
}

  header("location: " . autoUrl($tenant->getCodeId() . "/settings/direct-debit"));
