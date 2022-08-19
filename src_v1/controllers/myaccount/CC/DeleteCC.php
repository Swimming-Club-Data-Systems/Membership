<?php

$db = app()->db;

try {
  $db->beginTransaction();

  $count = $db->prepare("SELECT COUNT(*) FROM notifyAdditionalEmails WHERE UserID = ? AND ID = ?");
  $count->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $id]);
  $before = $count->fetchColumn();

  $delete = $db->prepare("DELETE FROM notifyAdditionalEmails WHERE UserID = ? AND ID = ?");
  $delete->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $id]);

  $count = $db->prepare("SELECT COUNT(*) FROM notifyAdditionalEmails WHERE UserID = ? AND ID = ?");
  $count->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $id]);
  $after = $count->fetchColumn();

  $db->commit();

  if ($after < $before) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['DeleteCCSuccess'] = true;
  }
} catch (Exception $e) {
  $db->rollBack();
}

header("Location: " . autoUrl("my-account/email#cc"));
