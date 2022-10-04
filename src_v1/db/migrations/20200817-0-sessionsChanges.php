<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `sessionsSquads` (
    `Squad` int(11) NOT NULL,
    `Session` int(11) NOT NULL,
    `ForAllMembers` boolean NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`Squad`, `Session`),
    FOREIGN KEY (`Squad`) REFERENCES squads(SquadID) ON DELETE CASCADE,
    FOREIGN KEY (`Session`) REFERENCES `sessions`(`SessionID`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

// Allow null attendance value
$db->query("ALTER TABLE `sessionsAttendance` 
  MODIFY `AttendanceBoolean` int DEFAULT NULL;");

$db->query("ALTER TABLE `sessionsAttendance` 
  ADD COLUMN `AttendanceRequired` boolean DEFAULT TRUE;");

// Get members and squads
$sessions = $db->query("SELECT `SessionID`, `SquadID`, `MainSequence` FROM `sessions`");

$addNew = $db->prepare("INSERT INTO `sessionsSquads` (`Squad`, `Session`, `ForAllMembers`) VALUES (?, ?, ?)");

// For each add to new table
while ($session = $sessions->fetch(PDO::FETCH_ASSOC)) {
  $addNew->execute([
    $session['SquadID'],
    $session['SessionID'],
    (int) $session['MainSequence'],
  ]);
}

// Modify sessions table
$db->query("ALTER TABLE `sessions` 
  DROP FOREIGN KEY `sessions_ibfk_1`,
  DROP INDEX `SquadID`,
  DROP SquadID,
  DROP MainSequence;");