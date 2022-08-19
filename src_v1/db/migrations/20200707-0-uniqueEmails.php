<?php

/**
 * Changes so same user email can be used once in each tenant
 */

$db->query("ALTER TABLE `users` DROP INDEX `EmailAddress`;");

$db->query("CREATE UNIQUE INDEX UniqueTenantEmail ON users(EmailAddress, Tenant);");