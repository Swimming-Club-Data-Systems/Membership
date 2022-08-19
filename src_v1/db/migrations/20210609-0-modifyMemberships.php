<?php

// Add type column
$db->query(
  "ALTER TABLE `clubMembershipClasses` 
  ADD `Type` varchar(256) DEFAULT 'club' AFTER `ID`;"
);

// Add new NGB membership class column to members
$db->query(
  "ALTER TABLE `members` 
  ADD `NGBCategory` char(36) DEFAULT NULL AFTER `ASACategory`;"
);

$addClass = $db->prepare("INSERT INTO `clubMembershipClasses` (`ID`, `Type`, `Name`, `Description`, `Fees`, `Tenant`) VALUES (?, ?, ?, ?, ?, ?)");
$updateMembers = $db->prepare("UPDATE `members` SET `NGBCategory` = ? WHERE `ASACategory` = ? AND `Tenant` = ?");

// Get tenants
$getTenants = $db->query("SELECT ID FROM tenants");
while ($tenant = $getTenants->fetchColumn()) {
  // Add Swim England classes for each tenant

  // LEVEL 1
  $l1 = \Ramsey\Uuid\Uuid::uuid4();
  $addClass->execute([
    $l1,
    'national_governing_body',
    'Swim England Category 1',
    'Cat 1',
    json_encode([
      'type' => 'PerPerson',
      'upgrade_type' => 'TopUp',
      'fees' => [1720],
    ]),
    $tenant,
  ]);

  // LEVEL 2
  $l2 = \Ramsey\Uuid\Uuid::uuid4();
  $addClass->execute([
    $l2,
    'national_governing_body',
    'Swim England Category 2',
    'Cat 2',
    json_encode([
      'type' => 'PerPerson',
      'upgrade_type' => 'TopUp',
      'fees' => [3575],
    ]),
    $tenant,
  ]);

  // LEVEL 3
  $l3 = \Ramsey\Uuid\Uuid::uuid4();
  $addClass->execute([
    $l3,
    'national_governing_body',
    'Swim England Category 3',
    'Cat 3',
    json_encode([
      'type' => 'PerPerson',
      'upgrade_type' => 'TopUp',
      'fees' => [1320],
    ]),
    $tenant,
  ]);

  $updateMembers->execute([
    $l1,
    1,
    $tenant,
  ]);

  $updateMembers->execute([
    $l2,
    2,
    $tenant,
  ]);

  $updateMembers->execute([
    $l3,
    3,
    $tenant,
  ]);
}

// Drop ASACategory column
$db->query(
  "ALTER TABLE `members` 
  DROP COLUMN `ASACategory`;"
);