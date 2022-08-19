<?php

$db->query(
  "ALTER TABLE `covidVisitors` ADD `Notes` TEXT NULL AFTER `Inputter`, ADD `SignedOut` BOOLEAN NOT NULL DEFAULT FALSE AFTER `Notes`; "
);