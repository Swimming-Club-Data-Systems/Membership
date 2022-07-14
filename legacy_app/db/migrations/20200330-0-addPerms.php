<?php

/**
 * ADD NEW PERMISSIONS CODE
 * SAME ACCOUNT DIFFERENT ACCESS LEVELS
 */

$db->query(
  "CREATE TABLE IF NOT EXISTS `permissions` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Permission` varchar(200) NOT NULL,
    `User` int(11) NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (User) REFERENCES users(UserID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "ALTER TABLE `squads` DROP FOREIGN KEY `squads_ibfk_1`;"
);

$db->query(
  "ALTER TABLE `squads` DROP INDEX `UserID`;"
);

$db->query(
  "ALTER TABLE `squads` DROP COLUMN `UserID`;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `coaches` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `User` int(11) NOT NULL,
    `Squad` int(11) NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (User) REFERENCES users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (Squad) REFERENCES squads(SquadID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);