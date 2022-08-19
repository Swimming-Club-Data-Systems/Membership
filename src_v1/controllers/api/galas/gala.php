<?php

/**
 * API for gala
 */

$db = app()->db;
$tenant = app()->tenant;

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
