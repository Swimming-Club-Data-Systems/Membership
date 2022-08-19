<?php

/**
 * API for tenant settings
 */

$db = app()->db;
$tenant = app()->tenant;

$output = [];

try {

  $tenantKeyData = [];
  $keys = [
    'CLUB_NAME' => 'string',
    'CLUB_SHORT_NAME' => 'string',
    'ASA_CLUB_CODE' => 'string',
    'CLUB_EMAIL' => 'string',
    'CLUB_TRIAL_EMAIL' => 'string',
    'CLUB_WEBSITE' => 'string',
    'GOCARDLESS_USE_SANDBOX' => 'bool',
    'CLUB_ADDRESS' => 'string',
    'SYSTEM_COLOUR' => 'string',
    'ASA_DISTRICT' => 'string',
    'ASA_COUNTY' => 'string',
    'EMERGENCY_MESSAGE' => 'string',
    'EMERGENCY_MESSAGE_TYPE' => 'string',
    'GALA_CARD_PAYMENTS_ALLOWED' => 'bool',
    'USE_DIRECT_DEBIT' => 'bool',
    'MEMBERSHIP_FEE_PM_CARD' => 'bool',
    'MEMBERSHIP_FEE_PM_DD' => 'bool',
    'ENABLE_BILLING_SYSTEM' => 'bool',
    'NGB_NAME' => 'string',
    'REQUIRE_SQUAD_REP_FOR_APPROVAL' => 'bool',
    'DISPLAY_NAME_FORMAT' => 'string',
    'DEFAULT_GALA_PROCESSING_FEE' => 'int',
    'LOGO_DIR' => 'string',
  ];

  foreach ($keys as $key => $type) {
    $var = $tenant->getkey($key);
    settype($var, $type);
    $tenantKeyData[mb_strtolower($key)] = $var;
  }

  $logoPath = null;
  $logoDir = $tenant->getKey('LOGO_DIR');
  if ($logoDir) {
    $logoPath = getUploadedAssetUrl($logoDir);
  }

  $output = [
    'success' => true,
    'keys' => $tenantKeyData,
    'tenant' => [
      'id' => $tenant->getId(),
      'uuid' => $tenant->getUuid(),
      'name' => $tenant->getName(),
      'is_verified' => (boolean) $tenant->isVerified(),
      'website' => $tenant->getWebsite(),
      'hostname' => $tenant->getDomain(),
      'name' => $tenant->getName(),
      'swim_england_code' => $tenant->getCode(),
      'club_logo_path' => $logoPath,
    ]
  ];

} catch (Exception $e) {
  $output = [
    'success' => false,
    'message' => $e->getMessage()
  ];
}

echo json_encode($output);
