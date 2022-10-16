<?php

$db->query(
  "ALTER TABLE users
    ADD COLUMN `ASANumber` varchar(255) DEFAULT NULL,
    ADD COLUMN `ASACategory` int(11) NOT NULL,
    ADD COLUMN `ASAPrimary` tinyint(1) DEFAULT TRUE NOT NULL,
    ADD COLUMN `ASAPaid` tinyint(1) DEFAULT FALSE NOT NULL,
    ADD COLUMN `ClubMember` tinyint(1) DEFAULT FALSE NOT NULL,
    ADD COLUMN `ClubCategory` int(11) DEFAULT NULL,
    ADD COLUMN `ClubPaid` tinyint(1) DEFAULT FALSE NOT NULL
  ;"
);

$db->query(
  "ALTER TABLE members
    ADD COLUMN `ASAPrimary` tinyint(1) DEFAULT TRUE NOT NULL,
    ADD COLUMN `ASAPaid` tinyint(1) DEFAULT FALSE NOT NULL,
    ADD COLUMN `ClubMember` tinyint(1) DEFAULT TRUE NOT NULL,
    ADD COLUMN `ClubCategory` int(11) DEFAULT 1,
    ADD COLUMN `ClubPaid` tinyint(1) DEFAULT FALSE NOT NULL
  ;"
);