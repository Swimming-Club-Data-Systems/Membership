<?php

/**
 * MEMBER MODS
 * 
 * ADDS SQUAD MEMBERS FOR MANY SQUADS TO MANY MEMBERS FUTURE
 */

$db->query(
  "ALTER TABLE `members` MODIFY SquadID int;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `squadMembers` (
    `Member` int NOT NULL,
    `Squad` int NOT NULL,
    PRIMARY KEY (`Member`, `Squad`),
    FOREIGN KEY (Member) REFERENCES members(MemberID) ON DELETE CASCADE,
    FOREIGN KEY (Squad) REFERENCES squads(SquadID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);