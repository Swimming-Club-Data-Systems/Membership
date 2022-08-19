<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `superUsers` (
    `ID` char(36) NOT NULL,
    `First` varchar(256) NOT NULL,
    `Last` varchar(256) NOT NULL,
    `PWHash` varchar(256) NOT NULL,
    `Email` varchar(256) NOT NULL,
    `TwoFactor` varchar(256) NOT NULL,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);