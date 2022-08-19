<?php

$db->query(
  "ALTER TABLE extras ADD `Type` enum('Payment','Refund') COLLATE utf8mb4_bin DEFAULT 'Payment';"
);