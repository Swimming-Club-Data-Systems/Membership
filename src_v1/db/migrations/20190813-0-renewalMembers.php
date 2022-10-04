<?php

$db->query(
  "ALTER TABLE renewalMembers ADD `Renewed` BOOLEAN DEFAULT '0';"
);