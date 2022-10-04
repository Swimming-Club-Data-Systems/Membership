<?php

$db->query(
  "ALTER TABLE paymentCategories
    ADD COLUMN `Show` boolean NOT NULL DEFAULT 1;"
);