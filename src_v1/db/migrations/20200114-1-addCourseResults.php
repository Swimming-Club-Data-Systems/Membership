<?php

$db->query(
  "ALTER TABLE meetsWithResults ADD `Course` char(1) NOT NULL;"
);