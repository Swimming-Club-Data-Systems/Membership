<?php

/**
 * Add stages for member onboarding
 * Add new renewal period system
 * Add renewal and type to onboarding system
 * Modify medical details //
 */

$db->query(
  "ALTER TABLE `memberMedical`
  ADD COLUMN `GPName` varchar(255) DEFAULT NULL,
  ADD COLUMN `GPAddress` JSON NOT NULL DEFAULT '[]',
  ADD COLUMN `GPPhone` varchar(255) DEFAULT NULL,
  ADD COLUMN WithholdConsent boolean DEFAULT FALSE NOT NULL,
  ADD CONSTRAINT valid_gp_address_json CHECK (JSON_VALID(`GPAddress`));"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `renewalv2` (
    `id` char(36) DEFAULT UUID() NOT NULL,
    `year` char(36) NOT NULL,
    `start` DATE NOT NULL,
    `end` DATE NOT NULL,
    `default_stages` JSON NOT NULL DEFAULT '[]',
    `default_member_stages` JSON NOT NULL DEFAULT '[]',
    `metadata` json NOT NULL,
    `Tenant` int(11) NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`year`) REFERENCES membershipYear(ID) ON DELETE CASCADE,
    FOREIGN KEY (`Tenant`) REFERENCES tenants(ID) ON DELETE CASCADE,
    CHECK (JSON_VALID(`default_stages`)),
    CHECK (JSON_VALID(`default_member_stages`)),
    CHECK (JSON_VALID(`metadata`))
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "ALTER TABLE `onboardingSessions`
  ADD COLUMN `type` varchar(255) DEFAULT 'onboarding' NOT NULL,
  ADD COLUMN `renewal` char(36) DEFAULT NULL;"
);

$db->query(
  "ALTER TABLE `onboardingMembers`
  ADD COLUMN `stages` json NOT NULL DEFAULT '[]',
  ADD CONSTRAINT valid_stages_json CHECK (JSON_VALID(`stages`));"
);
