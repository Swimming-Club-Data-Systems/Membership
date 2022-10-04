<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `onboardingSessions` (
    `id` char(36) NOT NULL DEFAULT UUID(),
    `user` int NOT NULL,
    `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    `creator` int,
    `start` DATE NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    `charge_outstanding` boolean NOT NULL DEFAULT FALSE,
    `charge_pro_rata` boolean NOT NULL DEFAULT FALSE,
    `welcome_text` text DEFAULT NULL,
    `token` varchar(1024) NOT NULL,
    `token_on` boolean NOT NULL DEFAULT FALSE,
    `status` varchar(255) DEFAULT NULL,
    `due_date` DATE,
    `completed_at` DATETIME DEFAULT NULL,
    `stages` JSON NOT NULL,
    `metadata` JSON NOT NULL,
    `batch` char(36),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`batch`) REFERENCES `membershipBatch`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user`) REFERENCES users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (`creator`) REFERENCES users(UserID) ON DELETE CASCADE,
    CHECK (JSON_VALID(`stages`)),
    CHECK (JSON_VALID(`metadata`))
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `onboardingMembers` (
    `id` char(36) NOT NULL DEFAULT UUID(),
    `session` char(36) NOT NULL,
    `member` int NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`session`) REFERENCES `onboardingSessions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`member`) REFERENCES members(MemberID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);