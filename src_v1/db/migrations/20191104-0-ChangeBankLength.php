<?php

$db->query(
  "ALTER TABLE `paymentMandates` MODIFY `BankName` VARCHAR(100);"
);