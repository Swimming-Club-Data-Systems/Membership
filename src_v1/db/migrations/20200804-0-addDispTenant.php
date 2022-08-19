<?php

$db->query(
  "ALTER TABLE stripeDisputes
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY sd_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);