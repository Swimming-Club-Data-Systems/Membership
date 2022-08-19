<?php

namespace SCDS\Onboarding;

use DateTimeZone;
use DateTime;
use Exception;

/**
 * Onboarding Session Class
 * 
 * @author Chris Heppell
 */
class Renewal
{
  public $id;
  public $clubYear;
  public $ngbYear;
  public $start;
  public $end;
  public $defaultStages;
  public $defaultMemberStages;
  public $metadata;

  private function __construct()
  {
  }

  public static function retrieve($renewal, $tenant = null)
  {
    $db = app()->db;
    if (!$tenant) $tenant = app()->tenant->getId();

    $get = $db->prepare("SELECT * FROM `renewalv2` WHERE renewalv2.id = ? AND renewalv2.Tenant = ?");
    $get->execute([
      $renewal,
      $tenant,
    ]);

    $sessionInfo = $get->fetch(\PDO::FETCH_OBJ);

    if (!$sessionInfo) throw new Exception('No such renewal period');

    $renewal = new Renewal();

    $renewal->id = $sessionInfo->id;
    $renewal->clubYear = $sessionInfo->club_year;
    $renewal->ngbYear = $sessionInfo->ngb_year;
    $renewal->start = new DateTime($sessionInfo->start, new DateTimeZone('Europe/London'));
    $renewal->end = new DateTime($sessionInfo->end, new DateTimeZone('Europe/London'));
    $renewal->end->setTime(23, 59, 59);
    $renewal->defaultStages = json_decode($sessionInfo->default_stages);
    $renewal->defaultMemberStages = json_decode($sessionInfo->default_member_stages);
    $renewal->metadata = json_decode($sessionInfo->metadata);
    if ($sessionInfo->club_year) $renewal->clubYear = \SCDS\Memberships\Year::retrieve($sessionInfo->club_year);
    if ($sessionInfo->ngb_year) $renewal->ngbYear = \SCDS\Memberships\Year::retrieve($sessionInfo->ngb_year);

    return $renewal;
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

  public function generateSessions()
  {
    // Check if sessions exist
    $db = app()->db;
    $tenant = app()->tenant;

    $db->beginTransaction();

    try {

      if ($this->isCreated()) throw new Exception("Sessions exist");

      // Get active users with a connected member!
      $getUsers = $db->prepare("SELECT `UserID` FROM `users` WHERE `Tenant` = ? AND `Active` AND `UserID` IN (SELECT `UserID` FROM `members` WHERE `Tenant` = ? AND `Active`)");
      $getUsers->execute([
        $tenant->getId(),
        $tenant->getId(),
      ]);

      while ($user = $getUsers->fetchColumn()) {
        $this->generateSession($user);
      }

      $db->commit();
    } catch (\Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }

  public function generateSession($user)
  {
    $db = app()->db;
    $tenant = app()->tenant;

    // If we aren't in a DB transaction, USE ONE!
    $thisControlsTransaction = !bool($db->inTransaction());

    if ($thisControlsTransaction) {
      $db->beginTransaction();
    }

    try {

      // Validate user
      $getCount = $db->prepare("SELECT COUNT(*) FROM `users` WHERE `UserID` = ? AND `Tenant` = ?");
      $getCount->execute([
        $user,
        $tenant->getId(),
      ]);

      if ($getCount->fetchColumn() == 0) throw new Exception("No user");

      // Check if session exists
      $getCount = $db->prepare("SELECT COUNT(*) FROM `onboardingSessions` WHERE `type` = ? AND `renewal` = ? AND `user` = ?");
      $getCount->execute([
        'renewal',
        $this->id,
        $user,
      ]);

      if ($getCount->fetchColumn() > 0) throw new Exception("Session exists");

      // Sort out making the onboarding session

      // Get members
      $getMembers = $db->prepare("SELECT `MemberID`, `DateOfBirth` FROM `members` WHERE `UserID` = ? ORDER BY `MForename` ASC, `MSurname` ASC;");
      $getMembers->execute([
        $user,
      ]);

      $members = [];
      $hasMemberUnder18 = false;
      while ($member = $getMembers->fetch(\PDO::FETCH_OBJ)) {
        $memberObj = new \Member($member->MemberID);
        $memberStages = clone $this->defaultMemberStages;

        // Don't require photo consent for adults
        if ($memberObj->getAge() >= 18) {
          $memberStages->photography_consent->required = false;
        } else {
          $hasMemberUnder18 = true;
        }

        $members[] = [
          'id' => $member->MemberID,
          'stages' => $memberStages,
        ];
      }

      // Put together membership batch
      $batch = \Ramsey\Uuid\Uuid::uuid4();

      $paymentTypes = $this->metadata->supported_payment_types;

      $total = 0;
      $batchItems = [];

      if ($this->ngbYear) {
        // Do SE fees
        $getMembers = $db->prepare("SELECT `MemberID`, `NGBCategory`, `ASAPaid`, `Name`, `Description`, `Fees` FROM `members` INNER JOIN `clubMembershipClasses` ON members.NGBCategory = clubMembershipClasses.ID WHERE `UserID` = ? ORDER BY `MForename` ASC, `MSurname` ASC;");
        $getMembers->execute([
          $user,
        ]);

        while ($member = $getMembers->fetch(\PDO::FETCH_OBJ)) {
          // Parse fee object
          $fees = json_decode($member->Fees);
          $amount = (int) $fees->fees[0];
          if ($member->ASAPaid) $amount = 0;
          $total += $amount;

          $batchItems[] = [
            \Ramsey\Uuid\Uuid::uuid4(),
            $batch,
            $member->NGBCategory,
            $member->MemberID,
            $amount,
            null,
            $this->ngbYear->id,
          ];
        }
      }

      if ($this->clubYear) {
        // Handle complex CLUB MEMBERSHIP fees
        // Get membership classes for this user's members
        $getClasses = $db->prepare("SELECT DISTINCT `ClubCategory` FROM `members` WHERE `UserID` = ?;");
        $getClasses->execute([
          $user,
        ]);

        while ($classId = $getClasses->fetchColumn()) {

          // Get full class details
          $getClassDetails = $db->prepare("SELECT `Name`, `Description`, `Fees` FROM clubMembershipClasses WHERE ID = ?");
          $getClassDetails->execute([
            $classId,
          ]);

          $classDetails = $getClassDetails->fetch(\PDO::FETCH_OBJ);

          $fees = json_decode($classDetails->Fees);

          // Get members with class

          $getClassMembers = $db->prepare("SELECT MemberID FROM members WHERE UserID = ? AND ClubCategory = ? AND ClubPaid = ?");

          $getCountClassMembers = $db->prepare("SELECT COUNT(*) FROM members WHERE UserID = ? AND ClubCategory = ? AND ClubPaid = ?");

          // Get paying
          $getCountClassMembers->execute([
            $user,
            $classId,
            0,
          ]);

          $count = $getCountClassMembers->fetchColumn();

          if ($count > 0 && $fees->type == "NSwimmers") {

            $amount = 0;
            if (isset($fees->fees[min($count, sizeof($fees->fees)) - 1])) $amount = (int) $fees->fees[min($count, sizeof($fees->fees)) - 1];
            $total += $amount;

            $getClassMembers->execute([
              $user,
              $classId,
              0,
            ]);

            $done = false;

            while ($member = $getClassMembers->fetchColumn()) {
              if ($done) {
                $amount = 0;
              }

              $batchItems[] = [
                \Ramsey\Uuid\Uuid::uuid4(),
                $batch,
                $classId,
                $member,
                $amount,
                null,
                $this->clubYear->id,
              ];

              $done = true;
            }
          } else if ($count > 0) {
            $amount = 0;
            if (isset($fees->fees[0])) $amount = (int) $fees->fees[0];

            $getClassMembers->execute([
              $user,
              $classId,
              0,
            ]);

            while ($member = $getClassMembers->fetchColumn()) {
              $total += $amount;

              $batchItems[] = [
                \Ramsey\Uuid\Uuid::uuid4(),
                $batch,
                $classId,
                $member,
                $amount,
                null,
                $this->clubYear->id,
              ];
            }
          }

          // Get non-paying
          $getClassMembers->execute([
            $user,
            $classId,
            1,
          ]);

          while ($member = $getClassMembers->fetchColumn()) {
            $amount = 0;

            $batchItems[] = [
              \Ramsey\Uuid\Uuid::uuid4(),
              $batch,
              $classId,
              $member,
              $amount,
              null,
              $this->clubYear->id,
            ];
          }
        }
      }

      // Add batch
      $add = $db->prepare("INSERT INTO `membershipBatch` (`ID`, `User`, `Total`, `PaymentTypes`, `PaymentDetails`) VALUES (?, ?, ?, ?, ?);");
      $add->execute([
        $batch,
        $user,
        $total,
        json_encode($paymentTypes),
        json_encode([]),
      ]);

      // Add batch items
      $addBatchItem = $db->prepare("INSERT INTO `membershipBatchItems` (`ID`, `Batch`, `Membership`, `Member`, `Amount`, `Notes`, `Year`) VALUES (?, ?, ?, ?, ?, ?, ?)");
      foreach ($batchItems as $batchItem) {
        $addBatchItem->execute($batchItem);
      }

      // Create onboarding session
      $id = \Ramsey\Uuid\Uuid::uuid4();
      $now = new DateTime('now', new DateTimeZone('UTC'));
      $today = new DateTime('now', new DateTimeZone('Europe/London'));
      $welcomeText = null;
      $stages = $this->defaultStages;
      $metadata = [];

      // Remove parent code of conduct if no under 18s
      if (!$hasMemberUnder18) $stages->parent_conduct->required = false;

      // Add to db
      $add = $db->prepare("INSERT INTO onboardingSessions (`id`,  `user`, `created`, `creator`, `start`, `charge_outstanding`, `charge_pro_rata`, `welcome_text`, `token`, `token_on`, `status`, `due_date`, `completed_at`, `stages`, `metadata`, `batch`, `type`, `renewal`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $add->execute([
        $id,
        $user,
        $now->format('Y-m-d H:i:s'),
        null,
        $today->format('Y-m-d'),
        (int) false,
        (int) false,
        $welcomeText,
        hash('sha256', random_int(PHP_INT_MIN, PHP_INT_MAX)),
        (int) false,
        'pending',
        null,
        null,
        json_encode($stages),
        json_encode($metadata),
        $batch,
        'renewal',
        $this->id,
      ]);

      // Add members
      $add = $db->prepare("INSERT INTO onboardingMembers (`id`, `session`, `member`, `stages`) VALUES (?, ?, ?, ?)");

      foreach ($members as $member) {
        $add->execute([
          \Ramsey\Uuid\Uuid::uuid4(),
          $id,
          $member['id'],
          json_encode($member['stages']),
        ]);
      }

      if ($thisControlsTransaction) {
        $db->commit();
      }
    } catch (\Exception $e) {
      if ($thisControlsTransaction) {
        $db->rollBack();
      } else {
        throw $e;
      }
    }
  }

  public function isCreated()
  {
    $db = app()->db;
    $getCount = $db->prepare("SELECT COUNT(*) FROM `onboardingSessions` WHERE `type` = ? AND `renewal` = ?");
    $getCount->execute([
      'renewal',
      $this->id,
    ]);

    return $getCount->fetchColumn() > 0;
  }
}
