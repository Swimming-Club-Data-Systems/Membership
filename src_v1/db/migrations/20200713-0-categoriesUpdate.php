<?php

$db->query("ALTER TABLE paymentCategories DROP COLUMN `Automatic`, ADD COLUMN UniqueID varchar(36) DEFAULT uuid() NOT NULL;");