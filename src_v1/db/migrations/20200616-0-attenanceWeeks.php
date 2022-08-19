<?php

use Ramsey\Uuid\Uuid;

$db->query(
  "ALTER TABLE sessionsWeek
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY sw_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE tenants
    ADD COLUMN `UniqueID` VARCHAR(36) NOT NULL DEFAULT UUID();"
);

$tenants = $db->query(
  "SELECT ID FROM tenants;"
);

$setUuid = $db->prepare(
  "UPDATE tenants SET UniqueID = ? WHERE ID = ?;"
);

while ($id = $tenants->fetchColumn()) {
  $uuid = Uuid::uuid4();
  $setUuid->execute([
    $uuid->toString(),
    $id,
  ]);
}