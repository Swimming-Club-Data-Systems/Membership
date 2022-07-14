<?php

$db->query(
  "ALTER TABLE users
    ADD COLUMN `Active` boolean DEFAULT 1
  ;"
);