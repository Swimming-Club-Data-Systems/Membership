<?php

$db->query(
  "ALTER TABLE `payments` ADD COLUMN `stripeFee` int NOT NULL DEFAULT 0;"
);