<?php

$db->query(
  "ALTER TABLE galaEntries ADD `PaymentID` INT;"
);

$db->query(
  "ALTER TABLE galaEntries ADD CONSTRAINT `FK_PaymentID` FOREIGN KEY (`PaymentID`) REFERENCES paymentsPending(PaymentID);"
);