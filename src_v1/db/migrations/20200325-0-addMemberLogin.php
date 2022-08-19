<?php

$db->query(
  "ALTER TABLE members
    ADD COLUMN `PWHash` varchar(200),
    ADD COLUMN `PWWrong` int DEFAULT 0
  ;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `memberEmailAddresses` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Member` int(11) NOT NULL,
    `EmailAddress` varchar(320) NOT NULL,
    `Verified` boolean DEFAULT 0,
    `Primary` boolean DEFAULT 0,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (Member) REFERENCES members(MemberID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);