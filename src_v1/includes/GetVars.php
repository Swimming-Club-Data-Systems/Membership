<?php

$db = app()->db;


$vars = [
  'CLUB_NAME' => null,
  'CLUB_SHORT_NAME' => null,
  'ASA_CLUB_CODE' => null,
  'CLUB_EMAIL' => null,
  'CLUB_TRIAL_EMAIL' => null,
  'EMAIL_DOMAIN' => null,
  'CLUB_WEBSITE' => null,
  'SENDGRID_API_KEY' => null,
  'GOCARDLESS_USE_SANDBOX' => null,
  'GOCARDLESS_SANDBOX_ACCESS_TOKEN' => null,
  'GOCARDLESS_ACCESS_TOKEN' => null,
  'GOCARDLESS_WEBHOOK_KEY' => null,
  'CLUB_ADDRESS' => null,
  'SYSTEM_COLOUR' => '#007bff',
  'ASA_DISTRICT' => 'E',
  'ASA_COUNTY' => 'NDRE',
  'STRIPE' => null,
  'STRIPE_PUBLISHABLE' => null,
  'STRIPE_APPLE_PAY_DOMAIN' => null,
  'EMERGENCY_MESSAGE' => false,
  'EMERGENCY_MESSAGE_TYPE' => 'NONE',
];

try {
  foreach ($vars as $key => $value) {
    if (getenv($key) == null) {
      $v = app()->tenant->getKey($key);
      if ($v != null) {
        if (!defined($key)) {
          define($key, $v);
        }
        putenv($key . "=" . $v);
      }
    } else {
      $systemInfo->setExistingEnvVar($key);
    }
  }
} catch (Exception $e) {
  halt(500);
}