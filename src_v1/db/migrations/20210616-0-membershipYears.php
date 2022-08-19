<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `membershipYear` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `Name` varchar(255) NOT NULL,
    `StartDate` date NOT NULL,
    `EndDate` date NOT NULL,
    `Tenant` int NOT NULL DEFAULT 1,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Tenant`) REFERENCES tenants(ID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `membershipYearMembers` (
    `Year` char(36) NOT NULL,
    `Member` int NOT NULL,
    FOREIGN KEY (`Year`) REFERENCES membershipYear(ID) ON DELETE CASCADE,
    FOREIGN KEY (`Member`) REFERENCES members(MemberID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `memberships` (
    `Member` int NOT NULL,
    `Year` char(36) NOT NULL,
    `Membership` char(36) NOT NULL,
    `Amount` int NOT NULL DEFAULT 0,
    `StartDate` date NOT NULL,
    `EndDate` date NOT NULL,
    `Purchased` datetime NOT NULL,
    `PaymentInfo` JSON NOT NULL,
    `Notes` text,
    FOREIGN KEY (`Year`) REFERENCES membershipYear(ID) ON DELETE CASCADE,
    FOREIGN KEY (`Member`) REFERENCES members(MemberID) ON DELETE CASCADE,
    FOREIGN KEY (`Membership`) REFERENCES clubMembershipClasses(ID) ON DELETE CASCADE,
    CHECK (JSON_VALID(`PaymentInfo`))
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);