<?php

$db->query(
  "ALTER TABLE users MODIFY COLUMN `ASACategory` int(11) DEFAULT 0 NOT NULL;"
);