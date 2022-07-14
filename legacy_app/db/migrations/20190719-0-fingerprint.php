<?php

$db->query(
  "ALTER TABLE stripePayMethods ADD `Fingerprint` varchar(255), ADD `Reusable` tinyint(1) NOT NULL DEFAULT '1';"
);

$db->query(
  "ALTER TABLE stripePayMethods MODIFY COLUMN ExpMonth int, MODIFY COLUMN ExpYear int;"
);