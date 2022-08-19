<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `tenantPaymentProducts` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `Name` varchar(256) NOT NULL,
    `Description` text,
    `Created` DATETIME NOT NULL DEFAULT current_timestamp(),
    `Updated` DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `tenantPaymentPlans` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `Product` char(36) NOT NULL,
    `PricePerUnit` int NOT NULL,
    `UsageType` varchar(256) NOT NULL,
    `Currency` char(3) NOT NULL,
    `BillingInterval` varchar(256) NOT NULL,
    `Name` varchar(256) NOT NULL,
    `Created` DATETIME NOT NULL DEFAULT current_timestamp(),
    `Updated` DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Product`) REFERENCES `tenantPaymentProducts`(`ID`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `tenantPaymentTaxRates` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `Name` varchar(256) NOT NULL,
    `Type` varchar(256) NOT NULL,
    `Region` varchar(256) NOT NULL,
    `Rate` int NOT NULL,
    `InclusiveExclusive` varchar(256) NOT NULL,
    `Created` DATETIME NOT NULL DEFAULT current_timestamp(),
    `Updated` DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `tenantPaymentSubscriptions` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `Customer` varchar(256) NOT NULL,
    `PaymentMethod` varchar(256) NOT NULL,
    `Memo` text,
    `Footer` text,
    `StartDate` DATE NOT NULL,
    `EndDate` DATE NOT NULL,
    `Active` boolean NOT NULL DEFAULT TRUE,
    `Created` DATETIME NOT NULL DEFAULT current_timestamp(),
    `Updated` DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Customer`) REFERENCES `tenantStripeCustomers`(`CustomerID`) ON DELETE CASCADE,
    FOREIGN KEY (`PaymentMethod`) REFERENCES `tenantPaymentMethods`(`MethodID`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `tenantPaymentSubscriptionProducts` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `Plan` char(36) NOT NULL,
    `Quantity` int NOT NULL,
    `NextBills` DATE NOT NULL,
    `TaxRate` char(36) NOT NULL,
    `Discount` int,
    `DiscountType` varchar(256),
    `Created` DATETIME NOT NULL DEFAULT current_timestamp(),
    `Updated` DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Plan`) REFERENCES `tenantPaymentPlans`(`ID`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);