<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `notifyCategories` (
    `ID` char(36) DEFAULT UUID() NOT NULL,
    `Name` varchar(255) NOT NULL,
    `Description` mediumtext NOT NULL,
    `Active` boolean DEFAULT TRUE,
    `Tenant` int(11) NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Tenant`) REFERENCES tenants(ID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);