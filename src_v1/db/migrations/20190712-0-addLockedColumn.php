<?php

$db->query(
  "ALTER TABLE galaEntries ADD `Locked` BOOLEAN DEFAULT '0', ADD `Vetoable` BOOLEAN DEFAULT '0';"
);