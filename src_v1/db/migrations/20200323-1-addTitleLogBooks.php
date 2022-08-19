<?php

$db->query(
  "ALTER TABLE trainingLogs
    ADD COLUMN `Title` varchar(150) NOT NULL
  ;"
);