<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `linkedAccounts` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `User` int(11) NOT NULL,
    `LinkedUser` int(11) NOT NULL,
    `Key` varchar(255) NULL,
    `Active` tinyint(1) NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (User) REFERENCES users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (LinkedUser) REFERENCES users(UserID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);