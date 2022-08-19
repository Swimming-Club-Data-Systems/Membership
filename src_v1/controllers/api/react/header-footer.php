<?php

/**
 * API for tenant settings
 */

$db = app()->db;
$tenant = app()->tenant;

$output = [];

try {

  ob_start();
  ob_clean();

  require BASE_PATH . 'views/generic/header-content.php';
  $header = ob_get_clean();

  // // ob_start();
  require BASE_PATH . 'views/generic/footer-content.php';
  $footer = ob_get_clean();

  $output = [
    'success' => true,
    'header' => $header,
    'footer' => $footer,
  ];

} catch (Exception | Error $e) {
  $output = [
    'success' => false,
    'message' => $e->getMessage()
  ];
}

echo json_encode($output);
