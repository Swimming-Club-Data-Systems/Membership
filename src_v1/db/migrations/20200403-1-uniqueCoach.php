<?php

/**
 * PERMISSIONS UNIQUE
 * SAME ACCOUNT DIFFERENT ACCESS LEVELS
 */

$db->query(
  "ALTER TABLE `coaches` ADD UNIQUE `unique_index`(`User`, `Squad`);"
);