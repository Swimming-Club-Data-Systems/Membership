<?php

$db->query(
  "ALTER TABLE tenantPaymentSubscriptionProducts 
  ADD `Subscription` char(36) NOT NULL DEFAULT UUID() AFTER `ID`,
  MODIFY COLUMN TaxRate char(36) DEFAULT NULL,
  ADD FOREIGN KEY sub_fk(Subscription) REFERENCES tenantPaymentSubscriptions(ID) ON DELETE CASCADE
  ; "
);

$db->query(
  "ALTER TABLE tenantPaymentSubscriptions MODIFY COLUMN EndDate DATE DEFAULT NULL;"
);