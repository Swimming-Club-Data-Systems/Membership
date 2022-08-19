<?php

/**
 * Class for adding items to the audit log
 * 
 * @author Chris Heppell
 * @copyright SCDS
 * 
 */
class AuditLog
{

  /**
   * Method for adding a new audit log event
   * 
   * @param string event type
   * @param string event description
   * @author Chris Heppell
   * @throws Exception if not active user
   */
  public static function new(string $event, string $description, int $user = null)
  {
    // Check user is set
    if (!$user) {
      if (!isset(app()->user)) {
        throw new Exception('No active user');
      }

      $user = app()->user->getId();
      if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserSimulation']['RealUser'])) {
        $user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserSimulation']['RealUser'];
      }
    }

    $time = new DateTime('now', new DateTimeZone('UTC'));

    $add = app()->db->prepare("INSERT INTO `auditLogging` (`ID`, `User`, `Time`, `Event`, `Description`) VALUES (?, ?, ?, ?, ?)");
    $add->execute([
      \Ramsey\Uuid\Uuid::uuid4()->toString(),
      $user,
      $time->format('Y-m-d H:i:s'),
      mb_strimwidth($event, 0, 256),
      mb_strimwidth($description, 0, 512),
    ]);
  }
}
