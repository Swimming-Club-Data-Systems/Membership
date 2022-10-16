<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `trainingLogs` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Member` int(11) NOT NULL,
    `DateTime` DATETIME NOT NULL,
    `Content` TEXT COLLATE utf8mb4_bin NOT NULL,
    `ContentType` varchar(100) COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (Member) REFERENCES members(MemberID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);