<?php

$db->query(
  "ALTER TABLE `stripeMandates` ADD COLUMN `CreationTime` DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL AFTER `MandateStatus`;"
);