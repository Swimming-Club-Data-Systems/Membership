<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `qualifications` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `Name` varchar(256) NOT NULL,
    `Description` text DEFAULT NULL,
    `DefaultExpiry` json NOT NULL,
    `Show` boolean NOT NULL DEFAULT TRUE,
    `Tenant` int NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Tenant`) REFERENCES `tenants`(`ID`) ON DELETE CASCADE,
    CHECK (JSON_VALID(`DefaultExpiry`))
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `qualificationsMembers` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `Qualification` char(36) NOT NULL,
    `Member` int NOT NULL,
    `ValidFrom` DATE NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    `ValidUntil` DATE,
    `Notes` text DEFAULT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Member`) REFERENCES `members`(`MemberID`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);