<?php

$db->query("ALTER TABLE `renewalMembers` 
  ADD COLUMN `PaymentType` ENUM('dd','cash','card','cheque','bacs','none') DEFAULT 'dd',
  ADD COLUMN `StripePayment` int DEFAULT NULL,
  ADD COLUMN `CashPaid` boolean DEFAULT FALSE,
  ADD COLUMN `ChequePaid` boolean DEFAULT FALSE,
  ADD COLUMN `BACSPaid` boolean DEFAULT FALSE,
  ADD COLUMN `BACSReference` varchar(18) DEFAULT NULL
  ;");