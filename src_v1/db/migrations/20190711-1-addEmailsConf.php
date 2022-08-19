<?php

$db->query(
  "ALTER TABLE notifyAdditionalEmails ADD `Hash` varchar(255), ADD `Verified` BOOLEAN DEFAULT '0';"
);