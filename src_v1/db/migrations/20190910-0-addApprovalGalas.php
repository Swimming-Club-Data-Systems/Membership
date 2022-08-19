<?php

$db->query(
  "ALTER TABLE galas ADD `RequiresApproval` BOOLEAN DEFAULT '0';"
);

$db->query(
  "ALTER TABLE galaEntries ADD `Approved` BOOLEAN DEFAULT '1';"
);