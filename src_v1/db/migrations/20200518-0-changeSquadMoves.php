<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `squadMoves` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Member` int(11) NOT NULL,
    `Date` date NOT NULL,
    `Old` int(11),
    `New` int(11),
    `Paying` BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (Old) REFERENCES squads(SquadID) ON DELETE CASCADE,
    FOREIGN KEY (New) REFERENCES squads(SquadID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query("DROP TABLE moves");