<?php

$db->query(
  "CREATE UNIQUE INDEX stripeCustomerId ON stripeCustomers (CustomerID);"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `stripeMandates` (
    `ID` varchar(255) NOT NULL,
    `Mandate` varchar(255) NOT NULL,
    `Customer` varchar(255) NOT NULL,
    `Fingerprint` varchar(255) NOT NULL,
    `Last4` varchar(4) NOT NULL,
    `SortCode` varchar(8) NOT NULL,
    `Address` varchar(1024) NOT NULL,
    `Status` enum('pending', 'revoked', 'refused', 'accepted') NOT NULL,
    `MandateStatus` enum('active', 'inactive', 'pending') NOT NULL,
    `Reference` varchar(64) NOT NULL,
    `URL` varchar(512) NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (Customer) REFERENCES stripeCustomers(CustomerID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "ALTER TABLE payments
  ADD COLUMN `stripeMandate` varchar(255) DEFAULT NULL,
  ADD COLUMN `stripePaymentIntent` varchar(255) DEFAULT NULL,
  ADD COLUMN `stripePayout` varchar(255) DEFAULT NULL;"
);

// $db->query(
//   "ALTER TABLE payments
//   ADD FOREIGN KEY sm(`stripeMandate`) REFERENCES stripeMandates(`ID`);"
// );

// $db->query(
//   "ALTER TABLE payments
//   ADD FOREIGN KEY sp(`stripePayout`) REFERENCES stripePayouts(`ID`);"
// );