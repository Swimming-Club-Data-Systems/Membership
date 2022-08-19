<?php

$db->query(
  "DROP TABLE IF EXISTS `covidRiskAwareness`"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `covidRiskAwareness` (
    `ID` char(36) NOT NULL,
    `DateTime` DateTime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `Member` int(11) NOT NULL,
    `MemberAgreement` boolean NOT NULL DEFAULT FALSE,
    `Guardian` int(11) DEFAULT NULL,
    `GuardianAgreement` boolean DEFAULT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Member`) REFERENCES members(MemberID) ON DELETE CASCADE,
    FOREIGN KEY (`Guardian`) REFERENCES users(UserID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);