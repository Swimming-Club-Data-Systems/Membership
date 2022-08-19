<?php

$db->query(
  "ALTER TABLE users
    ADD COLUMN `Country` VARCHAR(7) DEFAULT 'GB-ENG' NOT NULL
  ;"
);

$db->query(
  "ALTER TABLE members
    ADD COLUMN `Country` VARCHAR(7) DEFAULT 'GB-ENG' NOT NULL
  ;"
);