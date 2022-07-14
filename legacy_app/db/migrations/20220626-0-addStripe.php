<?php

/**
 * Add new Stripe PaymentIntent and PaymentMethod tables for generic payments
 * Allows payments from anonymous persons
 */

$db->query(
  "CREATE TABLE IF NOT EXISTS `stripePaymentMethods` (
    `id` char(36) DEFAULT UUID() NOT NULL,
    `stripe_id` varchar(255) NOT NULL,
    `customer` varchar(255) DEFAULT NULL,
    `billing_details` JSON NOT NULL,
    `type` varchar(256) NOT NULL,
    `type_data` JSON NOT NULL,
    `fingerprint` varchar(256) DEFAULT NULL,
    `reusable` BOOLEAN DEFAULT FALSE,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`stripe_id`),
    FOREIGN KEY (`customer`) REFERENCES stripeCustomers(CustomerID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `stripePaymentIntents` (
    `id` char(36) DEFAULT UUID() NOT NULL,
    `stripe_id` varchar(255) NOT NULL,
    `amount` int NOT NULL,
    `amount_capturable` int NOT NULL DEFAULT '0',
    `amount_received` int NOT NULL DEFAULT '0',
    `amount_refunded` int NOT NULL DEFAULT '0',
    `currency` char(3) DEFAULT 'gbp',
    `customer` varchar(255) DEFAULT NULL,
    `description` varchar(256) DEFAULT NULL,
    `payment_method` varchar(255) DEFAULT NULL,
    `shipping` JSON DEFAULT NULL,
    `charge_data` JSON DEFAULT NULL,
    `status` varchar(256) NOT NULL,
    `review` varchar(255) DEFAULT NULL,
    `application_fee_amount` int DEFAULT '0',
    `cancelled_at` DATETIME DEFAULT NULL,
    `cancellation_reason` varchar(256),
    `invoice` varchar(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`stripe_id`),
    FOREIGN KEY (`payment_method`) REFERENCES stripePaymentMethods(stripe_id) ON DELETE CASCADE,
    FOREIGN KEY (`customer`) REFERENCES stripeCustomers(CustomerID) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);