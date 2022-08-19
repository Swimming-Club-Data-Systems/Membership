<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `squadReps` (
    `User` int(11) NOT NULL,
    `Squad` int(11) NOT NULL,
    `ContactDescription` varchar(255) COLLATE utf8mb4_bin,
    PRIMARY KEY (`User`, `Squad`),
    FOREIGN KEY (User) REFERENCES users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (Squad) REFERENCES squads(SquadID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);