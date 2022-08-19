<?php

/**
 * Add covid meets table
 */

$db->query(
  "CREATE TABLE IF NOT EXISTS `meets` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Name` varchar(200) NOT NULL,
    `StartTime` DATETIME NOT NULL,
    `Creator` int(11) NOT NULL,
    `Started` boolean NOT NULL DEFAULT 0,
    `Finished` boolean NOT NULL DEFAULT 0,
    `Link` varchar(2048) NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (Creator) REFERENCES users(UserID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);