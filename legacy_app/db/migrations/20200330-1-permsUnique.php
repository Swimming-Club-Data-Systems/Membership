<?php

/**
 * PERMISSIONS UNIQUE
 * SAME ACCOUNT DIFFERENT ACCESS LEVELS
 */

$db->query(
  "ALTER TABLE `permissions` ADD UNIQUE `unique_index`(`User`, `Permission`);"
);