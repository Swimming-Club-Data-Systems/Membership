<?php

$db = app()->db;

// Verify that there are no db tables.
$getTableCount = $db->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE' AND `TABLE_SCHEMA` = ?");
$getTableCount->execute([
  getenv('DB_NAME')
]);

if ($getTableCount->fetchColumn() == 0) {
  // Run migrations system then add default admin
  // Default admin uses app()->tenant->getKey('CLUB_EMAIL') email if set
  // Else no admin is added
  include BASE_PATH . 'controllers/migrations/migrate.php';

  if (app()->tenant->getKey('CLUB_EMAIL')) {
    try {
      $addFirstUser = $db->prepare("INSERT INTO users (`Password`, EmailAddress, EmailComms, Forename, Surname, Mobile, MobileComms) VALUES (?, 'Admin', ?, 1, 'Primary', 'Admin', '+44123456789', 1)");
      $addFirstUser->execute([
        password_hash('DEFAULTPASSWORD', PASSWORD_BCRYPT),
        app()->tenant->getKey('CLUB_EMAIL')
      ]);

      $uid = $db->lastInsertId();

      $addAccessLevel = $db->prepare("INSERT INTO `permissions` (`Permission`, `User`) VALUES (?, ?)");
      $addAccessLevel->execute([
        'Admin',
        $uid
      ]);
    } catch (Exception $e) {
      // Could not add user
    }
  }
} else {
  halt(404);
}