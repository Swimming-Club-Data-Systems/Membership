<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `sessionsBookable` (
    `Session` int(11) NOT NULL,
    `Date` Date NOT NULL,
    `MaxPlaces` int(11) DEFAULT NULL,
    `AllSquads` boolean DEFAULT FALSE,
    PRIMARY KEY (`Session`, `Date`),
    FOREIGN KEY (`Session`) REFERENCES `sessions`(`SessionID`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `sessionsBookings` (
    `Session` int(11) NOT NULL,
    `Date` Date NOT NULL,
    `Member` int(11) NOT NULL,
    `BookedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`Session`, `Date`, `Member`),
    FOREIGN KEY (`Session`) REFERENCES `sessions`(`SessionID`) ON DELETE CASCADE,
    FOREIGN KEY (`Member`) REFERENCES members(MemberID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);
