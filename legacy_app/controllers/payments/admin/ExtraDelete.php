<?php

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();

try {
  $delete = $db->prepare("DELETE FROM extras WHERE ExtraID = ? AND Tenant = ?");
  $delete->execute([
    $id,
    $tenant->getId()
  ]);
  header("Location: " . autoUrl("payments/extrafees"));
} catch (Exception $e) {
  halt(500);
}
