<?php

$db->query(
  "ALTER TABLE users ADD `WrongPassCount` INT NOT NULL DEFAULT 0;"
);