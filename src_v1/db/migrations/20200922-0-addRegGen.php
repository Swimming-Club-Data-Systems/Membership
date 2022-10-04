<?php

$db->query("ALTER TABLE `sessionsBookable` 
  ADD COLUMN `RegisterGenerated` boolean DEFAULT FALSE;");