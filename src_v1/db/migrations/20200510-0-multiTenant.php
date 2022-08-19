<?php

/**
 * Migration to a multi-tenant application
 * 
 * Needs to add tenants table
 * Add tenant id to
 * 
 * extras
 * galas
 * joinParents/Swimmers
 * REMOVE linkedAccounts
 * meets ?????
 * meetsWithResults
 * members //
 * newUsers -> redo new users?
 * notifyHistory
 * paymentCategories
 * paymentMonths?
 * paymentsPayouts
 * paymentSquadFees
 * posts
 * qualifications
 * renewals
 * sessions
 * sessionsVenues
 * squads
 * stripePayouts
 * tenantOptions -> tenantOptions //
 * targetedLists
 * REMOVE TIMES
 * users -> modified /
 * 
 */

$db->query(
  "CREATE TABLE IF NOT EXISTS `tenants` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Name` varchar(128) NOT NULL,
    `Code` char(4),
    `Website` varchar(256)  ,
    `Email` varchar(256) NOT NULL,
    `Verified` tinyint(1) NOT NULL DEFAULT 0,
    UNIQUE (Code),
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "INSERT INTO `tenants` (`ID`, `Name`, `Code`, `Website`, `Email`, `Verified`) VALUES (1, 'Default Tenant', 'XXXX', 'https://myswimmingclub.uk/', 'hello@example.com', 0);"
);

$db->query(
  "RENAME TABLE systemOptions TO tenantOptions;"
);

$db->query(
  "ALTER TABLE tenantOptions
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY options_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE members
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY members_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE users
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY users_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE extras
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY extras_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE galas
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY galas_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE meetsWithResults
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY mwr_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE notifyHistory
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY notify_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE paymentCategories
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY pc_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE paymentMonths
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY pmonths_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE paymentsPayouts
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY pp_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE paymentSquadFees
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY psf_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE posts
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY p_oststenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE qualifications
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY quals_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE renewals
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY renewals_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE `sessions`
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY sessions_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE sessionsVenues
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY sv_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE squads
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY squads_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE stripePayouts
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY stripePayouts_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE targetedLists
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY tl_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE joinParents
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY jp_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE joinSwimmers
    ADD COLUMN `Tenant` int NOT NULL DEFAULT 1,
    ADD FOREIGN KEY js_tenant(Tenant) REFERENCES tenants(ID) ON DELETE CASCADE;"
);

$db->query(
  "DROP TABLE IF EXISTS times;"
);