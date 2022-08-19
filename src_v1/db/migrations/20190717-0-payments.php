<?php

$db->query(
  "ALTER TABLE galaEntries ADD `StripePayment` int DEFAULT NULL, ADD FOREIGN KEY pay(StripePayment) REFERENCES stripePayments(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE stripePayments ADD `AmountRefunded` int DEFAULT '0';"
);

$db->query(
  "ALTER TABLE stripePaymentItems ADD `AmountRefunded` int DEFAULT '0';"
);