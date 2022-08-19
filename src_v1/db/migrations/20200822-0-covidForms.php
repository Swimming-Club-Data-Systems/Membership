<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `covidHealthScreen` (
    `ID` char(36) NOT NULL,
    `Member` int(11) NOT NULL,
    `DateTime` DateTime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `OfficerApproval` boolean NOT NULL DEFAULT FALSE,
    `ApprovedBy` int DEFAULT NULL,
    `Document` Text,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Member`) REFERENCES members(MemberID) ON DELETE CASCADE,
    FOREIGN KEY (`ApprovedBy`) REFERENCES users(UserID) ON DELETE SET NULL ON UPDATE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `covidRiskAwareness` (
    `ID` char(36) NOT NULL,
    `Member` int(11) NOT NULL,
    `DateTime` DateTime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `OfficerApproval` boolean NOT NULL DEFAULT FALSE,
    `ApprovedBy` int DEFAULT NULL,
    `MemberAgreement` boolean NOT NULL DEFAULT FALSE,
    `GuardianAgreement` boolean NOT NULL DEFAULT FALSE,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Member`) REFERENCES members(MemberID) ON DELETE CASCADE,
    FOREIGN KEY (`ApprovedBy`) REFERENCES users(UserID) ON DELETE SET NULL ON UPDATE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);