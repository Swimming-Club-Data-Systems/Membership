<?php

$db->query(
  "ALTER TABLE meetResults ADD `Member` int NOT NULL;"
);

$db->query(
  "ALTER TABLE meetResults ADD CONSTRAINT `FK_MemberID` FOREIGN KEY (`Member`) REFERENCES members(MemberID);"
);