<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `tenantRedirect` (
    `ID` char(36) DEFAULT UUID() NOT NULL,
    `Source` varchar(256) NOT NULL,
    `Target` varchar(256) DEFAULT NULL,
    `Created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `Tenant` int(11) NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Tenant`) REFERENCES tenants(ID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);