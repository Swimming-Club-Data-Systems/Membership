<?php

/**
 * PERMISSIONS UNIQUE
 * SAME ACCOUNT DIFFERENT ACCESS LEVELS
 */

$users = $db->query(
  "SELECT AccessLevel, UserID FROM users"
);

$addToPerms = $db->prepare("INSERT INTO `permissions` (`Permission`, `User`) VALUES (?, ?)");

while ($user = $users->fetch(PDO::FETCH_ASSOC)) {
  try {
    $addToPerms->execute([
      $user['AccessLevel'],
      $user['UserID']
    ]);
  } catch (PDOException $e) {
    // Do nothing
  }

  setUserOption($user['UserID'], 'DefaultAccessLevel', $user['AccessLevel']);
}

$db->query(
  "ALTER TABLE `users` DROP COLUMN `AccessLevel`;"
);