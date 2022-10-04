<?php

/**
 * Support STRIPE payment statuses
 */

$db->query(
  "ALTER TABLE `payments` MODIFY `Status` enum('pending_api_request','pending_customer_approval','pending_submission','submitted','confirmed','paid_out','cancelled','customer_approval_denied','failed','charged_back','cust_not_dd','paid_manually','requires_payment_method','requires_confirmation','requires_action','processing','succeeded','canceled') NOT NULL;"
);

$db->query(
  "ALTER TABLE `paymentRetries` MODIFY `PMKey` VARCHAR(256) NOT NULL;"
);

$db->query(
  "ALTER TABLE `payments` ADD COLUMN `stripeFailureCode` VARCHAR(256);"
);