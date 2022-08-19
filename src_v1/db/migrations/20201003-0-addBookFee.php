<?php

$db->query("ALTER TABLE `sessionsBookable` 
  ADD COLUMN `BookingFee` int DEFAULT 0;");