<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `tenantStripeCustomers` (
    `Tenant` int NOT NULL,
    `CustomerID` varchar(256) NOT NULL,
    PRIMARY KEY (`CustomerID`),
    FOREIGN KEY (`Tenant`) REFERENCES `tenants`(`ID`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `tenantPaymentMethods` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `Customer` varchar(256) NOT NULL,
    `MethodID` varchar(256) NOT NULL,
    `MandateID` varchar(256),
    `Fingerprint` varchar(256),
    `Type` varchar(256) NOT NULL,
    `JSON` JSON,
    `Usable` boolean DEFAULT FALSE,
    `Created` DATETIME NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`MethodID`),
    FOREIGN KEY (`Customer`) REFERENCES `tenantStripeCustomers`(`CustomerID`) ON DELETE CASCADE,
    CHECK (JSON_VALID(`JSON`))
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);