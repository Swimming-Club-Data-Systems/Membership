<?php

$db->query(
  "ALTER TABLE `extrasRelations` DROP FOREIGN KEY `extrasRelations_ibfk_1`; ALTER TABLE `extrasRelations` ADD CONSTRAINT `extrasRelations_ibfk_1` FOREIGN KEY (`ExtraID`) REFERENCES `extras`(`ExtraID`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `extrasRelations` DROP FOREIGN KEY `extrasRelations_ibfk_2`; ALTER TABLE `extrasRelations` ADD CONSTRAINT `extrasRelations_ibfk_2` FOREIGN KEY (`MemberID`) REFERENCES `members`(`MemberID`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `extrasRelations` DROP FOREIGN KEY `extrasRelations_ibfk_3`; ALTER TABLE `extrasRelations` ADD CONSTRAINT `extrasRelations_ibfk_3` FOREIGN KEY (`UserID`) REFERENCES `users`(`UserID`) ON DELETE CASCADE ON UPDATE CASCADE;"
);