<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `renewalPeriods` (
    `ID` char(36) NOT NULL,
    `Opens` DATE NOT NULL,
    `Closes` JSON NOT NULL,
    `Name` varchar(256) NOT NULL,
    `Year` int NOT NULL,
    `Tenant` int NOT NULL DEFAULT 1,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Tenant`) REFERENCES tenants(ID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);


$db->query(
  "CREATE TABLE IF NOT EXISTS `renewalData` (
    `ID` char(36) NOT NULL,
    `Renewal` char(36) DEFAULT NULL,
    `User` int(11) DEFAULT NULL,
    `Document` JSON NOT NULL,
    `PaymentIntent` int,
    `PaymentDD` int,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Renewal`) REFERENCES renewalPeriods(ID) ON DELETE CASCADE,
    FOREIGN KEY (`User`) REFERENCES users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (`PaymentIntent`) REFERENCES stripePayments(ID) ON DELETE CASCADE,
    FOREIGN KEY (`PaymentDD`) REFERENCES paymentsPending(PaymentID) ON DELETE CASCADE,
    CHECK (JSON_VALID(`Document`))
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);
