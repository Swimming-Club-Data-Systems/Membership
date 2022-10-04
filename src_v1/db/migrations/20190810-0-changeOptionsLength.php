<?php

$db->query(
  "ALTER TABLE tenantOptions MODIFY `Option` varchar(255);"
);