<?php

$db->query(
  "ALTER TABLE members ADD `RRTransfer` BOOLEAN DEFAULT '0';"
);