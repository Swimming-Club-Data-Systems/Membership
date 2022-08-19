<?php

$db = app()->db;
$tenant = app()->tenant;

$success = true;

try  {

  if (!isset($_POST['name'])) throw new Exception('No name');
  if (!isset($_POST['description'])) throw new Exception('No description');

  if (mb_strlen(trim($_POST['name'])) == 0) throw new Exception('No name');
  if (mb_strlen(trim($_POST['description'])) == 0) throw new Exception('No description');

  $add = $db->prepare("INSERT INTO notifyCategories (`ID`, `Name`, `Description`, `Active`, `Tenant`) VALUES (?, ?, ?, ?, ?)");
  $add->execute([
    \Ramsey\Uuid\Uuid::uuid4(),
    trim($_POST['name']),
    trim($_POST['description']),
    (int) true,
    $tenant->getId(),
  ]);

} catch (Exception $e) {
  $success = false;
}

header('content-type: application/json');
echo json_encode([
  'success' => $success,
]);