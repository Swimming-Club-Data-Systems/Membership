<?php

use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

$db->query(
  "ALTER TABLE clubMembershipClasses
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY clubMembershipClasses_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE users
    DROP COLUMN ASANumber,
    DROP COLUMN ASACategory,
    DROP COLUMN ASAPrimary,
    DROP COLUMN ASAPaid,
    DROP COLUMN ClubMember,
    DROP COLUMN ClubCategory,
    DROP COLUMN ClubPaid,
    DROP COLUMN ASAMember,
    DROP COLUMN Country;"
);

$db->query(
  "ALTER TABLE members
    DROP COLUMN ClubCategory,
    ADD COLUMN ClubCategory char(36) NOT NULL DEFAULT UUID();"
);

$db->query(
  "UPDATE members SET ClubPaid = ClubPays, ASAPaid = ClubPays;"
);

$db->query(
  "ALTER TABLE members
    DROP COLUMN ClubPays;"
);

$getTenants = $db->query("SELECT `ID` FROM `tenants`;");
$get = $db->prepare("SELECT `Value` FROM `tenantOptions` WHERE `Option` = ? AND `Tenant` = ?");
$insert = $db->prepare("INSERT INTO `clubMembershipClasses` (`ID`, `Name`, `Description`, `Fees`, `Tenant`) VALUES (?, ?, ?, ?, ?)");
$updateMembers = $db->prepare("UPDATE `members` SET `ClubCategory` = ? WHERE `Tenant` = ?");

while ($tenant = $getTenants->fetchColumn()) {
  // Get fees
  $get = $db->prepare("SELECT `Value` FROM `tenantOptions` WHERE `Option` = ? AND `Tenant` = ?");
  $get->execute([
    'ClubFeeNSwimmers',
    $tenant
  ]);
  $fees = $get->fetchColumn();

  $get = $db->prepare("SELECT `Value` FROM `tenantOptions` WHERE `Option` = ? AND `Tenant` = ?");
  $get->execute([
    'ClubFeeUpgradeType',
    $tenant
  ]);
  $upgradeType = $get->fetchColumn();

  $get->execute([
    'ClubFeesType',
    $tenant
  ]);
  $type = $get->fetchColumn();

  if ($fees == null) {
    $fees = [];
  } else {
    $fees = json_decode($fees);
  }

  if ($upgradeType == null) {
    $upgradeType = 'TopUp';
  }

  if ($type == null) {
    $type = 'NSwimmers';
  }

  $newObject = [
    'type' => $type,
    'upgrade_type' => $upgradeType,
    'fees' => $fees,
  ];

  $json = json_encode($newObject);

  $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();
  $insert->execute([
    $uuid,
    'Club Membership (Standard)',
    null,
    $json,
    $tenant,
  ]);

  $updateMembers->execute([
    $uuid,
    $tenant,
  ]);

}