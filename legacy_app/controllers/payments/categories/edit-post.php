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

  if (!\SCDS\CSRF::verify()) {
    throw new Exception('Invalid CSRF token');
  }

  $update = $db->prepare("UPDATE paymentCategories SET `Name` = ?, `Description` = ? WHERE ID = ?;");

  if (mb_strlen(trim($_POST['category-name'])) == 0 || mb_strlen(trim($_POST['category-description'])) == 0) {
    throw new Exception('No name or description provided');
  }

  $update->execute([
    mb_strimwidth($_POST['category-name'], 0, 100),
    mb_strimwidth($_POST['category-description'], 0, 200),
    $category['ID'],
  ]);

  $_SESSION['TENANT-' . app()->tenant->getId()]['SaveCategorySuccess'] = true;
} catch (PDOException $e) {
  throw new Exception('A database error occurred');
} catch (Exception $e) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['SaveCategoryError'] = $e->getMessage();
}

header("location: " . autoUrl('payments/categories/' . $id));
