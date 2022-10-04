<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `superUsersLogins` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `User` char(36) NOT NULL,
    `Time` timestamp NOT NULL DEFAULT current_timestamp(),
    `IPAddress` varchar(256) NOT NULL,
    `GeoLocation` varchar(512) DEFAULT NULL,
    `Browser` varchar(256) NOT NULL,
    `Platform` varchar(256) NOT NULL,
    `Mobile` boolean NOT NULL DEFAULT FALSE,
    `Hash` varchar(512) NOT NULL,
    `HashActive` boolean NOT NULL DEFAULT FALSE,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`User`) REFERENCES `superUsers`(`ID`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `auditLogging` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `User` int NOT NULL,
    `Time` timestamp NOT NULL DEFAULT current_timestamp(),
    `Event` varchar(256) NOT NULL,
    `Description` varchar(512) NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`User`) REFERENCES `users`(`UserID`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);