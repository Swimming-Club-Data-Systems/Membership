<?php

$db->query(
  "ALTER TABLE `targetedListMembers` DROP FOREIGN KEY `targetedListMembers_ibfk_2`;"
);

$db->query(
  "ALTER TABLE `targetedListMembers` DROP INDEX `ReferenceID`;"
);