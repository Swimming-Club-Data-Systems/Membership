<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `teamManagers` (
    `User` int(11) NOT NULL,
    `Gala` int(11) NOT NULL,
    PRIMARY KEY (`User`, `Gala`),
    FOREIGN KEY (User) REFERENCES users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (Gala) REFERENCES galas(GalaID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);