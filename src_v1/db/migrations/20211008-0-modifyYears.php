<?php

$db->query(
  "ALTER TABLE `renewalv2`
  DROP FOREIGN KEY `renewalv2_ibfk_1`,
  DROP COLUMN `year`,
  ADD COLUMN `club_year` char(36) DEFAULT NULL,
  ADD COLUMN `ngb_year` char(36) DEFAULT NULL,
  ADD FOREIGN KEY (`club_year`) REFERENCES membershipYear(ID) ON DELETE CASCADE,
  ADD FOREIGN KEY (`ngb_year`) REFERENCES membershipYear(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE `membershipBatch`
  DROP FOREIGN KEY `membershipBatch_ibfk_2`,
  DROP COLUMN `Year`;"
);

$db->query(
  "ALTER TABLE `membershipBatchItems`
  ADD COLUMN `Year` char(36) NOT NULL;"
);

$db->query(
  "ALTER TABLE `membershipBatchItems`
  ADD CONSTRAINT `itemYear` FOREIGN KEY (`Year`) REFERENCES membershipYear(ID) ON DELETE CASCADE;"
);