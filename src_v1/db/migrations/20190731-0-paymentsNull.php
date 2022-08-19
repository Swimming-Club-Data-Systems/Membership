<?php

$db->query(
  "ALTER TABLE payments CHANGE `PMkey` `PMkey` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL, CHANGE `MandateID` `MandateID` INT(11) NULL;"
);