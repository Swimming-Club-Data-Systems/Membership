<?php

$db->query(
  "ALTER TABLE `tenants` 
  ADD `Domain` varchar(256) DEFAULT NULL AFTER `UniqueID`;"
);