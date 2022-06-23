<?php

/**
 * A migration to add WebAuthn credentials store
 */

$db->query(
  "CREATE TABLE IF NOT EXISTS `userCredentials` (
    `id` char(36) DEFAULT UUID() NOT NULL,
    `credential_id` varchar(256) NOT NULL,
    `credential_name` varchar(256) DEFAULT '',
    `user_id` int NOT NULL,
    `credential`  JSON NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES users(UserID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);