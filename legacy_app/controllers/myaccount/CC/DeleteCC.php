<?php

$db = DB::connection()->getPdo();

try {
  $db->beginTransaction();

  $count = $db->prepare("SELECT COUNT(*) FROM notifyAdditionalEmails WHERE UserID = ? AND ID = ?");
  $count->execute([$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'], $id]);
  $before = $count->fetchColumn();

  $delete = $db->prepare("DELETE FROM notifyAdditionalEmails WHERE UserID = ? AND ID = ?");
  $delete->execute([$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'], $id]);

  $count = $db->prepare("SELECT COUNT(*) FROM notifyAdditionalEmails WHERE UserID = ? AND ID = ?");
  $count->execute([$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'], $id]);
  $after = $count->fetchColumn();

  $db->commit();

  if ($after < $before) {
    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['DeleteCCSuccess'] = true;
  }
} catch (Exception $e) {
  $db->rollBack();
}

header("Location: " . autoUrl("my-account/email#cc"));
