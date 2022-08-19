<?php

$db->query(
  "ALTER TABLE emergencyContacts ADD COLUMN IF NOT EXISTS Relation VARCHAR(255);"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `paymentsPayouts` (
    `ID` varchar(30),
    `Amount` int(11) NOT NULL,
    `Fees` int(11) NOT NULL,
    `Currency` char(3) COLLATE utf8mb4_bin NOT NULL,
    `ArrivalDate` DATE DEFAULT NULL,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "ALTER TABLE payments ADD COLUMN IF NOT EXISTS Payout VARCHAR(30) DEFAULT NULL,
  ADD FOREIGN KEY payments(Payout) REFERENCES paymentsPayouts(ID) ON DELETE CASCADE;;"
);