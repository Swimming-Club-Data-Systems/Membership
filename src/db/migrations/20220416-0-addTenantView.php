<?php

/**
 * This file is to create SQL views based on the existing SQL tables. These
 * will be in the ActiveRecord format.
 * 
 * For this we need to;
 * 1. Add created_at and updated_at columns to tables as appropriate
 * 2. Add lock version where appropriate?
 * 3. Add deleted_at for soft deletes if appropriate
 * 4. Create VIEWs for each table which will be prefixed "x_" so they can have
 *      the same names as existing tables when required
 * 5. Use snake_case for column names
 * 6. Use pluralised snake_case for table names
 * 7. "id" for primary key, singlular "order_id" foreign key format
 * 
 * This should be one of the last every .php migration files in this project.
 * The aim after this is to move to Laravel's tools for migrations and seeding
 * etc.
 */

// throw new Exception("WIP: Exception to prevent execution");

/**
 * Add views
 */


$db->query("ALTER TABLE tenants ADD COLUMN `Data` JSON;");

$db->query("CREATE VIEW x_tenants AS SELECT ID id, `Name` `name`, Code code, Website website, Email email, Verified verified, UniqueID uuid, Domain domain, `Data` `data`, created_at, updated_at FROM tenants;");