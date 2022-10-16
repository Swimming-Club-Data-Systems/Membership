<?php

$db->query(
  "ALTER TABLE stripePayments ADD COLUMN IF NOT EXISTS Fees int DEFAULT 0;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `stripePayouts` (
    `ID` varchar(255),
    `Amount` int(11) NOT NULL,
    `ArrivalDate` DATE DEFAULT NULL,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);