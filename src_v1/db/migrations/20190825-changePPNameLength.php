<?php

$db->query(
  "ALTER TABLE paymentsPending MODIFY `Name` varchar(500);"
);