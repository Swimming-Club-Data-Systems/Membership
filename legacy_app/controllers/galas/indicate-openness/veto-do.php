<?php

$db = nezamy_app()->db;
$tenant = nezamy_app()->tenant;

$sql = null;

if ($_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['AccessLevel'] == "Parent") {
  $sql = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE `EntryID` = ? AND members.UserID = ? ORDER BY `galas`.`GalaDate` DESC;");
  $sql->execute([
    $id,
    $_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['UserID']
  ]);
} else {
  $sql = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE `EntryID` = ? AND galas.Tenant = ? ORDER BY `galas`.`GalaDate` DESC;");
  $sql->execute([
    $id,
    $tenant->getId()
  ]);
}
$row = $sql->fetch(PDO::FETCH_ASSOC);

if ($row == null || !$row['Vetoable']) {
  halt(404);
}

try {
  $update = $db->prepare("UPDATE galaEntries SET 50Free = :fal, 100Free = :fal, 200Free = :fal, 400Free = :fal, 800Free = :fal, 1500Free = :fal, 50Back = :fal, 100Back = :fal, 200Back = :fal, 50Breast = :fal, 100Breast = :fal, 200Breast = :fal, 50Fly = :fal, 100Fly = :fal, 200Fly = :fal, 100IM = :fal, 150IM = :fal, 200IM = :fal, 400IM = :fal, FeeToPay = :zero, Charged = :tru WHERE EntryID = :entryCode");
  $update->bindValue('fal', false, PDO::PARAM_BOOL);
  $update->bindValue('tru', true, PDO::PARAM_BOOL);
  $update->bindValue('zero', 0, PDO::PARAM_INT);
  $update->bindValue('entryCode', $id, PDO::PARAM_INT);
  $update->execute();
  $_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['VetoTrue'] = true;
} catch (Exception $e) {
  halt(500);
}

header("Location: " . autoUrl("galas/entries"));