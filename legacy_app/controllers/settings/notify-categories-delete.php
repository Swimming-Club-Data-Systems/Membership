<?php

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();

$success = true;

try {

  if (!isset($_POST['category'])) throw new Exception('No category');

  $check = $db->prepare("SELECT COUNT(*) FROM `notifyCategories` WHERE `ID` = ? AND `Tenant` = ? AND Active");
  $check->execute([
    $_POST['category'],
    $tenant->getId(),
  ]);

  if ($check->fetchColumn() != 1) throw new Exception('No category');

  $update = $db->prepare("UPDATE `notifyCategories` SET `Active` = ? WHERE `ID` = ?");
  $update->execute([
    (int) false,
    $_POST['category'],
  ]);
} catch (Exception $e) {
  $success = false;
}

header('content-type: application/json');
echo json_encode([
  'success' => $success,
]);
