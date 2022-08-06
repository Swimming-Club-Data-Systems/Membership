<?php

/**
 * API for gala
 */

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();

$output = [];

try {

  $gala = \SCDS\Galas\Gala::getGala($id);
  $output = $gala->getAttributes();

} catch (Exception $e) {
  $output = [
    'success' => false,
    'message' => $e->getMessage()
  ];
}

echo json_encode($output);
