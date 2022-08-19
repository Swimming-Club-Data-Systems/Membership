<?php

$db->query(
  "ALTER TABLE `renewalMembers` ADD FOREIGN KEY (`StripePayment`) REFERENCES `stripePayments`(`ID`) ON DELETE CASCADE;"
);