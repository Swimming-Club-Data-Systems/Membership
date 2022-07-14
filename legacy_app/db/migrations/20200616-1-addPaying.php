<?php

$db->query(
  "ALTER TABLE squadMembers
    ADD COLUMN `Paying` BOOLEAN NOT NULL DEFAULT TRUE;"
);