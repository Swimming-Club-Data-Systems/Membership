<?php

namespace SCDS\Memberships;

use DateTimeZone;
use DateTime;
use Exception;

/**
 * Onboarding Session Class
 * 
 * @author Chris Heppell
 */
class Year
{
  public $id;
  public $year;
  public $start;
  public $end;
  public $defaultStages;
  public $defaultMemberStages;
  public $metadata;

  private function __construct()
  {
  }

  public static function retrieve($id, $tenant = null)
  {
    $db = app()->db;
    if (!$tenant) $tenant = app()->tenant->getId();

    $get = $db->prepare("SELECT * FROM `membershipYear` WHERE ID = ? AND Tenant = ?");
    $get->execute([
      $id,
      $tenant,
    ]);

    $sessionInfo = $get->fetch(\PDO::FETCH_OBJ);

    if (!$sessionInfo) throw new Exception('No such membership year');

    $year = new Year();

    $year->id = $sessionInfo->ID;
    $year->name = $sessionInfo->Name;
    $year->start = new DateTime($sessionInfo->StartDate, new DateTimeZone('Europe/London'));
    $year->end = new DateTime($sessionInfo->EndDate, new DateTimeZone('Europe/London'));

    return $year;
  }

  public function isCurrent()
  {
    $today = new DateTime('now', new DateTimeZone('Europe/London'));
    return $this->start < $today && $today < $this->end;
  }

  public function isPast()
  {
    $today = new DateTime('now', new DateTimeZone('Europe/London'));
    return $today > $this->end;
  }
}
