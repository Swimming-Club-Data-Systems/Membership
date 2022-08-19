<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `tenantPaymentIntents` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `IntentID` varchar(256) NOT NULL,
    `PaymentMethod` varchar(256),
    `Review` varchar(256),
    `Amount` int NOT NULL,
    `Currency` char(3) NOT NULL,
    `Status` varchar(256),
    `Shipping` json,
    `Created` DATETIME NOT NULL DEFAULT current_timestamp(),
    `Updated` DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`IntentID`),
    FOREIGN KEY (`PaymentMethod`) REFERENCES `tenantPaymentMethods`(`MethodID`) ON DELETE CASCADE,
    CHECK (JSON_VALID(`Shipping`))
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `tenantPaymentInvoices` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `Reference` char(36) NOT NULL DEFAULT UUID(),
    `Customer` varchar(256) NOT NULL,
    `PaymentIntent` varchar(256),
    `Date` date DEFAULT current_timestamp(),
    `SupplyDate` date DEFAULT current_timestamp(),
    `Company` json,
    `Currency` char(3) NOT NULL,
    `PaymentTerms` text,
    `HowToPay` text,
    `PurchaseOrderNumber` varchar(256),
    `AmountPaidCash` int DEFAULT NULL,
    `PaidDate` date DEFAULT NULL,
    `Paid` boolean NOT NULL DEFAULT FALSE,
    `Created` DATETIME NOT NULL DEFAULT current_timestamp(),
    `Updated` DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Customer`) REFERENCES `tenantStripeCustomers`(`CustomerID`) ON DELETE CASCADE,
    FOREIGN KEY (`PaymentIntent`) REFERENCES `tenantPaymentIntents`(`IntentID`) ON DELETE CASCADE,
    CHECK (JSON_VALID(`Company`))
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `tenantPaymentInvoiceItems` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `Invoice` char(36) NOT NULL DEFAULT UUID(),
    `Description` json NOT NULL,
    `Amount` int NOT NULL,
    `Currency` char(3),
    `Type` enum('credit','debit'),
    `Quantity` int DEFAULT 1,
    `PricePerUnit` int NOT NULL,
    `VATAmount` int DEFAULT NULL,
    `VATRate` int DEFAULT NULL,
    `Created` DATETIME NOT NULL DEFAULT current_timestamp(),
    `Updated` DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Invoice`) REFERENCES `tenantPaymentInvoices`(`ID`) ON DELETE CASCADE,
    CHECK (JSON_VALID(`Description`))
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);