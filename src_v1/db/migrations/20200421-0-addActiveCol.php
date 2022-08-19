<?php

$db->query(
  "ALTER TABLE members
    ADD COLUMN `Active` boolean DEFAULT 1
  ;"
);