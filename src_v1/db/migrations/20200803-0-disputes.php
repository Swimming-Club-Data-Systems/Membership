<?php

// Disputes table for Stripe payments

// ID holds UUID
// SID holds stripe object id
$db->query(
  "CREATE TABLE IF NOT EXISTS `stripeDisputes` (
    `ID` varchar(255) NOT NULL,
    `SID` varchar(255) NOT NULL,
    `Amount` int NOT NULL DEFAULT 0,
    `Currency` varchar(3) NOT NULL,
    `PaymentIntent` varchar(255),
    `Reason` varchar(255) NOT NULL,
    `Status` varchar(255) NOT NULL,
    `Created` DATETIME,
    `EvidenceDueBy` DATETIME,
    `IsRefundable` boolean NOT NULL DEFAULT FALSE,
    `HasEvidence` boolean NOT NULL DEFAULT FALSE,
    `EvidencePastDue` boolean NOT NULL DEFAULT FALSE,
    `EvidenceSubmissionCount` int NOT NULL DEFAULT 0,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);