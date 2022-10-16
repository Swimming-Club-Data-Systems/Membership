<?php

$tenant = app()->tenant;
$db = app()->db;

$get = $db->prepare("SELECT `ID`, `Name`, `Description` FROM paymentCategories WHERE UniqueID = ? AND Tenant = ?");
$get->execute([
  $id,
  $tenant->getId(),
]);

$category = $get->fetch(PDO::FETCH_ASSOC);

if (!$category) {
  halt(404);
}

try {

  $update = $db->prepare("UPDATE paymentCategories SET `Show` = 0 WHERE ID = ?;");

  $update->execute([
    $category['ID'],
  ]);

  $_SESSION['TENANT-' . app()->tenant->getId()]['HideCategorySuccess'] = $category['Name'];
  header("location: " . autoUrl('payments/categories/'));
} catch (PDOException $e) {
  throw new Exception('A database error occurred');
} catch (Exception $e) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['SaveCategoryError'] = $e->getMessage();
  header("location: " . autoUrl('payments/categories/' . $id));
}
