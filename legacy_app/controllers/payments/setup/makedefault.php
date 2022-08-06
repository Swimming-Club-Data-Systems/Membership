<?php

$db = DB::connection()->getPdo();

try {
  $getMandatesCount = $db->prepare("SELECT COUNT(*) FROM `paymentMandates` WHERE `MandateID` = ? AND `UserID` = ? AND `InUse` = ?");
  $getMandatesCount->execute([
    $id,
    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'],
    true
  ]);

  if ($getMandatesCount->fetchColumn() </*!=*/ 1) {
  	halt(404);
  }
} catch (Exception $e) {
  halt(500);
}

try {
  $updateDefault = $db->prepare("UPDATE `paymentPreferredMandate` SET `MandateID` = ? WHERE `UserID` = ?");
  $updateDefault->execute([$id, $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID']]);
  header("Location: " . autoUrl("payments/mandates"));
} catch (Exception $e) {
  halt(500);
}
