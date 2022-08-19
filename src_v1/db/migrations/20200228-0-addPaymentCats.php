<?php

// Automatic means galas etc
$db->query(
  "CREATE TABLE IF NOT EXISTS `paymentCategories` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Name` varchar(100) COLLATE utf8mb4_bin NOT NULL,
    `Description` varchar(200) COLLATE utf8mb4_bin NOT NULL,
    `Automatic` boolean NOT NULL DEFAULT FALSE,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "ALTER TABLE stripePaymentItems
    ADD COLUMN `Category` int(11) DEFAULT NULL,
    ADD FOREIGN KEY pcat(Category) REFERENCES paymentCategories(ID) ON DELETE RESTRICT
  ;"
);

$db->query(
  "ALTER TABLE paymentsPending
    ADD COLUMN `Category` int(11) DEFAULT NULL,
    ADD FOREIGN KEY pcat2(Category) REFERENCES paymentCategories(ID) ON DELETE RESTRICT
  ;"
);

$db->query(
  "ALTER TABLE galas
    ADD COLUMN `PaymentCategory` int(11) DEFAULT NULL,
    ADD FOREIGN KEY pcat3(PaymentCategory) REFERENCES paymentCategories(ID) ON DELETE RESTRICT
  ;"
);