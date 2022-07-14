<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `galaSessions` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Gala` int(11) NOT NULL,
    `Name` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (Gala) REFERENCES galas(GalaID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `galaSessionsCanEnter` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Member` int(11) NOT NULL,
    `Session` int(11) NOT NULL,
    `CanEnter` tinyint(1) NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (Member) REFERENCES members(MemberID) ON DELETE CASCADE,
    FOREIGN KEY (`Session`) REFERENCES galaSessions(ID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "ALTER TABLE galas ADD `CoachEnters` BOOLEAN DEFAULT '0';"
);