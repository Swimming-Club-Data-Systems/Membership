<?php

$db->query(
  "ALTER TABLE `galaEntries` 
  ADD `25Free` boolean DEFAULT NULL AFTER `Charged`,
  ADD `25Breast` boolean DEFAULT NULL AFTER `1500Free`,
  ADD `25Fly` boolean DEFAULT NULL AFTER `200Breast`,
  ADD `25Back` boolean DEFAULT NULL AFTER `200Fly`,
  ADD `25FreeTime` boolean DEFAULT NULL AFTER `150IM`,
  ADD `25BreastTime` boolean DEFAULT NULL AFTER `1500FreeTime`,
  ADD `25FlyTime` boolean DEFAULT NULL AFTER `200BreastTime`,
  ADD `25BackTime` boolean DEFAULT NULL AFTER `200FlyTime`;"
);