<?php

$db->query(
  "ALTER TABLE `galas`
  ADD COLUMN `ProcessingFee` int DEFAULT 0;"
);

$db->query(
  "ALTER TABLE `galaEntries`
  ADD COLUMN `ProcessingFee` int DEFAULT 0;"
);