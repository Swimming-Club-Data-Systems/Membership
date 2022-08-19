<?php

$db->query(
  "DROP TABLE `tenantPaymentMethods`;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `tenantPaymentMethods` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `MethodID` varchar(256) NOT NULL,
    `Customer` varchar(256) NOT NULL,
    `BillingDetails` JSON,
    `Type` varchar(256) NOT NULL,
    `TypeData` JSON,
    `Fingerprint` varchar(256),
    `Usable` boolean DEFAULT FALSE,
    `Created` DATETIME NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`MethodID`),
    FOREIGN KEY (`Customer`) REFERENCES `tenantStripeCustomers`(`CustomerID`) ON DELETE CASCADE,
    CHECK (JSON_VALID(`BillingDetails`)),
    CHECK (JSON_VALID(`TypeData`))
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `tenantPaymentMandates` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `MandateID` varchar(256) NOT NULL,
    `AcceptanceData` JSON NOT NULL,
    `PaymentMethod` varchar(256) NOT NULL,
    `MethodDetails` JSON NOT NULL,
    `Status` varchar(256) NOT NULL,
    `UsageType` varchar(256) NOT NULL,
    `UsageData` JSON,
    `Created` DATETIME NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`MandateID`),
    FOREIGN KEY (`PaymentMethod`) REFERENCES `tenantPaymentMethods`(`MethodID`) ON DELETE CASCADE,
    CHECK (JSON_VALID(`AcceptanceData`)),
    CHECK (JSON_VALID(`MethodDetails`)),
    CHECK (JSON_VALID(`UsageData`))
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);