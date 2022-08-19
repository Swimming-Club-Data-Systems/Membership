<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `gcCredentials` (
    `OrganisationId` varchar(256) NOT NULL,
    `AccessToken` varchar(256) NOT NULL,
    `Tenant` int NOT NULL,
    PRIMARY KEY (`OrganisationId`, `Tenant`),
    FOREIGN KEY (Tenant) REFERENCES tenants(ID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);