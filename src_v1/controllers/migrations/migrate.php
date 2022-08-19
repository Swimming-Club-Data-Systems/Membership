<?php

$db = app()->db;

try {
  $exists = $db->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'schemaMigrations'");

  if ($exists->fetch(PDO::FETCH_ASSOC) == null) {
    // Create the DB Migrations table
    $db->query
    ("CREATE TABLE schemaMigrations (
      id int NOT NULL AUTO_INCREMENT,
      migration varchar(30) NOT NULL,
      PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");
  }
} catch (Exception $e) {
  pre($e);
}

$getMigrations = $db->query("SELECT migration FROM schemaMigrations ORDER BY id ASC");

$insertDoneMigration = $db->prepare("INSERT INTO schemaMigrations (migration) VALUES (?)");

$migrations = [];
while ($migration = $getMigrations->fetchColumn()) {
  $migrations[] = $migration . '.php';
}

pre($migrations);

$files = array_diff(scandir(BASE_PATH . 'db/migrations'), ['.', '..']);
pre($files);

$undone = array_diff($files, $migrations);
pre($undone);

foreach($undone as $file) {
  try {
    // Begin transaction
    $db->beginTransaction();

    // Run the migration file
    include BASE_PATH . 'db/migrations/' . $file;

    // Add to schema table
    $insertDoneMigration->execute([str_replace('.php', '', $file)]);

    // Commit changes to the database
    $db->commit();
  } catch (Exception $e) {

    // If it goes wrong, undo
    $db->rollBack();

    // Report this file was an error
    $_SESSION['MigrationErrorOccurred'][$file] = true;

    pre($e);
  }
}