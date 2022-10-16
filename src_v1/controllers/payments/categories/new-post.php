<?php

$db = app()->db;
$tenant = app()->tenant;

try {

  if (!\SCDS\CSRF::verify()) {
    throw new Exception('Invalid CSRF token');
  }

  $add = $db->prepare("INSERT INTO paymentCategories (`Name`, `Description`, `Tenant`, `UniqueID`) VALUES (?, ?, ?, ?)");

  if (mb_strlen(trim($_POST['category-name'])) == 0 || mb_strlen(trim($_POST['category-description'])) == 0) {
    throw new Exception('No name or description provided');
  }

  $uuid = Ramsey\Uuid\Uuid::uuid4();

  $add->execute([
    mb_strimwidth($_POST['category-name'], 0, 100),
    mb_strimwidth($_POST['category-description'], 0, 200),
    $tenant->getId(),
    $uuid->toString(),
  ]);

  // Get ID to find UUID
  $lastId = $db->lastInsertId();

  $get = $db->prepare("SELECT UniqueID FROM paymentCategories WHERE ID = ?");
  $get->execute([
    $lastId
  ]);

  $uniqueId = $get->fetchColumn();

  if (!$uniqueId) {
    throw new Exception('Unknown database error');
  }

  $_SESSION['TENANT-' . app()->tenant->getId()]['NewCategorySuccess'] = true;
  header("location: " . autoUrl('payments/categories/' . $uniqueId));
} catch (PDOException $e) {
  throw new Exception('A database error occurred');
} catch (Exception $e) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['NewCategoryError'] = $e->getMessage();
  header("location: " . autoUrl('payments/categories/new'));
}
