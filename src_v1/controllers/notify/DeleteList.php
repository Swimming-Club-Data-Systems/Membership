<?php

$db = app()->db;
$tenant = app()->tenant;

$sql = $db->prepare("SELECT COUNT(*) FROM `targetedLists` WHERE `ID` = ? AND `Tenant` = ?;");
$sql->execute([$id, $tenant->getId()]);
if ($sql->fetchColumn() == 0) {
  halt(404);
}

$sql = $db->prepare("DELETE FROM `targetedLists` WHERE `ID` = ? AND `Tenant` = ?;");
$sql->execute([$id, $tenant->getId()]);

header("Location: " . autoUrl("notify/lists"));
