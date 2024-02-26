<?php

$db = app()->db;
$tenant = app()->tenant;

$success = true;

try  {

  if (!isset($_POST['name'])) throw new Exception('No name');
  if (!isset($_POST['description'])) throw new Exception('No description');

  if (mb_strlen(trim((string) $_POST['name'])) == 0) throw new Exception('No name');
  if (mb_strlen(trim((string) $_POST['description'])) == 0) throw new Exception('No description');

  $add = $db->prepare("INSERT INTO notifyCategories (`ID`, `Name`, `Description`, `Active`, `Tenant`) VALUES (?, ?, ?, ?, ?)");
  $add->execute([
    \Ramsey\Uuid\Uuid::uuid4(),
    trim((string) $_POST['name']),
    trim((string) $_POST['description']),
    (int) true,
    $tenant->getId(),
  ]);

} catch (Exception) {
  $success = false;
}

header('content-type: application/json');
echo json_encode([
  'success' => $success,
]);