<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `membershipBatch` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `User` int NOT NULL,
    `Year` char(36) NOT NULL,
    `StartText` text,
    `Footer` text,
    `DueDate` date,
    `Completed` boolean NOT NULL DEFAULT FALSE,
    `Cancelled` boolean NOT NULL DEFAULT FALSE,
    `Total` int NOT NULL,
    `PaymentTypes` JSON NOT NULL,
    `PaymentDetails` JSON NOT NULL,
    `AutoReminders` boolean NOT NULL DEFAULT FALSE,
    `Creator` int,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`User`) REFERENCES users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (`Year`) REFERENCES membershipYear(ID) ON DELETE CASCADE,
    FOREIGN KEY (`Creator`) REFERENCES users(UserID) ON DELETE CASCADE,
    CHECK (JSON_VALID(`PaymentTypes`)),
    CHECK (JSON_VALID(`PaymentDetails`))
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `membershipBatchItems` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `Batch` char(36) NOT NULL,
    `Membership` char(36) NOT NULL,
    `Member` int NOT NULL,
    `Amount` int NOT NULL,
    `Notes` text,
    FOREIGN KEY (`Batch`) REFERENCES membershipBatch(ID) ON DELETE CASCADE,
    FOREIGN KEY (`Membership`) REFERENCES clubMembershipClasses(ID) ON DELETE CASCADE,
    FOREIGN KEY (`Member`) REFERENCES members(MemberID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);