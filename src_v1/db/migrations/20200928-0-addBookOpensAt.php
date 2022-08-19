<?php

$db->query("ALTER TABLE `sessionsBookable` 
  ADD COLUMN `BookingOpens` DATETIME DEFAULT NULL;");