<?php

// Temporary Contact Tracing support

$db->query(
  "CREATE TABLE IF NOT EXISTS `covidLocations` (
    `ID` char(36) NOT NULL,
    `Name` varchar(256) NOT NULL,
    `Address` TEXT NOT NULL,
    `Tenant` int NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Tenant`) REFERENCES tenants(ID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `covidVisitors` (
    `ID` char(36) NOT NULL,
    `Location` char(36) NOT NULL,
    `Time` DATETIME NOT NULL,
    `Person` int,
    `Type` enum('member','user','guest') NOT NULL,
    `GuestName` varchar(256),
    `GuestPhone` varchar(256),
    `Inputter` int,
    `Tenant` int NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`Location`) REFERENCES covidLocations(ID) ON DELETE CASCADE,
    FOREIGN KEY (`Inputter`) REFERENCES users(UserID) ON DELETE SET NULL,
    FOREIGN KEY (`Tenant`) REFERENCES tenants(ID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);