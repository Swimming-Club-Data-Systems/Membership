<?php

$db->query(
  "DROP TABLE `qualifications`"
);

$db->query(
  "DROP TABLE `qualificationsAvailable`"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `clubMembershipClasses` (
    `ID` char(36) NOT NULL DEFAULT UUID(),
    `Name` varchar(256) NOT NULL,
    `Description` text DEFAULT NULL,
    `Fees` json NOT NULL,
    PRIMARY KEY (`ID`),
    CHECK (JSON_VALID(`Fees`))
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);