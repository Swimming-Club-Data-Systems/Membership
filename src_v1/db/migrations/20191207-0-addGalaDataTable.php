<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `listSenders` (
    `User` int(11) NOT NULL,
    `List` int(11) NOT NULL,
    `Manager` boolean NOT NULL,
    PRIMARY KEY (`User`, `List`),
    FOREIGN KEY (User) REFERENCES users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (List) REFERENCES targetedLists(ID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `galaData` (
    `Gala` int(11) NOT NULL,
    `Events` TEXT NOT NULL,
    `Prices` TEXT NOT NULL,
    PRIMARY KEY (`Gala`),
    FOREIGN KEY (Gala) REFERENCES galas(GalaID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);