<?php

$db = app()->db;
$tenant = app()->tenant;

$success = true;

try  {

  if (!isset($_POST['category'])) throw new Exception('No category');

  $check = $db->prepare("SELECT COUNT(*) FROM `notifyCategories` WHERE `ID` = ? AND `Tenant` = ? AND Active");
  $check->execute([
    $_POST['category'],
    $tenant->getId(),
  ]);

  if ($check->fetchColumn() != 1) throw new Exception('No category');

  if (!isset($_POST['name'])) throw new Exception('No name');
  if (!isset($_POST['description'])) throw new Exception('No description');

  if (mb_strlen(trim($_POST['name'])) == 0) throw new Exception('No name');
  if (mb_strlen(trim($_POST['description'])) == 0) throw new Exception('No description');

  $update = $db->prepare("UPDATE `notifyCategories` SET `Name` = ?, `Description` = ? WHERE `ID` = ?");
  $update->execute([
    trim($_POST['name']),
    trim($_POST['description']),
    $_POST['category']
  ]);

} catch (Exception $e) {
  $success = false;
}

header('content-type: application/json');
echo json_encode([
  'success' => $success,
]);