<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `meetsWithResults` (
    `Meet` int(11) NOT NULL AUTO_INCREMENT,
    `Gala` int(11),
    `Name` varchar(100) COLLATE utf8mb4_bin NOT NULL,
    `City` varchar(50) COLLATE utf8mb4_bin NOT NULL,
    `Start` date,
    `End` date,
    PRIMARY KEY (`Meet`),
    FOREIGN KEY (Gala) REFERENCES galas(GalaID) ON DELETE SET NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `meetResults` (
    `Result` int(11) NOT NULL AUTO_INCREMENT,
    `Meet` int(11) NOT NULL,
    `Date` date NOT NULL,
    `Time` varchar(8) NOT NULL,
    `IntTime` int(11) NOT NULL,
    `ChronologicalOrder` int(11) NOT NULL,
    `Round` char(1) NOT NULL,
    `Stroke` char(1) NOT NULL,
    `Distance` int(11) NOT NULL,
    `Course` char(1) NOT NULL,
    PRIMARY KEY (`Result`),
    FOREIGN KEY (Meet) REFERENCES meetsWithResults(Meet) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);