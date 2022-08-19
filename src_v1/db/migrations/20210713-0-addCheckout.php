<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `checkoutSessions` (
    `id` char(36) NOT NULL DEFAULT UUID(),
    `user` int,
    `amount` int NOT NULL,
    `currency` char(3) NOT NULL,
    `state` varchar(64) NOT NULL,
    `allowed_types` JSON NOT NULL,
    `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    `succeeded` DATETIME DEFAULT NULL,
    `intent` varchar(255) DEFAULT NULL,
    `method` varchar(255) DEFAULT NULL,
    `version` varchar(64) DEFAULT 'v1',
    `creator` int,
    `tax_id` varchar(255) DEFAULT NULL,
    `total_details` JSON NOT NULL,
    `metadata` JSON NOT NULL,
    `Tenant` int NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`Tenant`) REFERENCES `tenants`(`ID`) ON DELETE CASCADE,
    FOREIGN KEY (`user`) REFERENCES users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (`creator`) REFERENCES users(UserID) ON DELETE CASCADE,
    CHECK (JSON_VALID(`allowed_types`)),
    CHECK (JSON_VALID(`total_details`)),
    CHECK (JSON_VALID(`metadata`))
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `checkoutItems` (
    `id` char(36) NOT NULL DEFAULT UUID(),
    `checkout_session` char(36) NOT NULL,
    `name` varchar(255) NOT NULL,
    `description` text,
    `amount` int NOT NULL,
    `currency` char(3) NOT NULL,
    `tax_amount` int NOT NULL DEFAULT 0,
    `tax_data` JSON NOT NULL,
    `sub_items` JSON NOT NULL,
    `type` varchar(255) DEFAULT 'debit',
    `attributes` JSON NOT NULL,
    `metadata` JSON NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`checkout_session`) REFERENCES checkoutSessions(id) ON DELETE CASCADE,
    CHECK (JSON_VALID(`tax_data`)),
    CHECK (JSON_VALID(`sub_items`)),
    CHECK (JSON_VALID(`attributes`)),
    CHECK (JSON_VALID(`metadata`))
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);
