<?php

$db->query(
  "ALTER TABLE `members` 
  ADD `GenderIdentity` varchar(256),
  ADD `GenderPronouns` varchar(256),
  ADD `GenderDisplay` boolean DEFAULT FALSE;"
);