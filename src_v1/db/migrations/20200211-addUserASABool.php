<?php

$db->query(
  "ALTER TABLE users
    ADD COLUMN `ASAMember` tinyint(1) DEFAULT FALSE NOT NULL
  ;"
);

$db->query(
  "ALTER TABLE members
    ADD COLUMN `ASAMember` tinyint(1) DEFAULT TRUE NOT NULL
  ;"
);