<?php

/**
 * Close galas at a datetime not a date
 * 
 */

$db->query("ALTER TABLE `galas` MODIFY `ClosingDate` DATETIME;");

$db->query("UPDATE `galas` SET `ClosingDate` = `ClosingDate` + INTERVAL '23:59' HOUR_MINUTE;");