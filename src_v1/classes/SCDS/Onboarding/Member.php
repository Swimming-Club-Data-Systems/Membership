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
class Member
{
  public $id;
  public $member;
  public $session;
  public $stages;
  private $currentStage;

  private function __construct()
  {
  }

  public static function retrieve($member, $session, $tenant = null)
  {
    $db = app()->db;
    if (!$tenant) $tenant = app()->tenant->getId();

    $get = $db->prepare("SELECT * FROM `onboardingMembers` INNER JOIN members ON members.MemberID = onboardingMembers.member WHERE `member` = ? AND `session` = ? AND `Tenant` = ?");
    $get->execute([
      $member,
      $session,
      $tenant,
    ]);

    $sessionInfo = $get->fetch(\PDO::FETCH_OBJ);

    if (!$sessionInfo) throw new Exception('No onboarding session member');

    $member = new Member();

    $member->id = $sessionInfo->id;
    $member->member = $sessionInfo->member;
    $member->session = Session::retrieve($sessionInfo->session);
    $member->stages = json_decode($sessionInfo->stages);

    return $member;
  }

  public static function retrieveById($id)
  {
    $db = app()->db;
    $get = $db->prepare("SELECT `member`, `session` FROM onboardingMembers WHERE `id` = ?");
    $get->execute([
      $id
    ]);

    $res = $get->fetch(\PDO::FETCH_OBJ);

    if ($res) {
      return Member::retrieve($res->member, $res->session);
    }

    throw new \Exception('Not found');
  }

  private function loadMembers()
  {
    $db = app()->db;
    $getMembers = $db->prepare("SELECT MemberID, MForename, MSurname FROM members INNER JOIN onboardingMembers ON members.MemberID = onboardingMembers.member WHERE `session` = ? AND `UserID` = ? ORDER BY MemberID ASC");
    $getMembers->execute([
      $this->id,
      $this->user,
    ]);

    $members = [];

    while ($member = $getMembers->fetch(\PDO::FETCH_OBJ)) {
      $memberObject = new \stdClass;

      $memberObject->id = $member->MemberID;
      $memberObject->firstName = $member->MForename;
      $memberObject->lastName = $member->MSurname;

      $members[] = $memberObject;
    }

    $this->members = $members;
  }

  private function getUrl()
  {
    return autoUrl("onboarding/go?session=" . urlencode($this->id) . "&token=" . urlencode($this->token));
  }

  public function getMember()
  {
    return new \Member($this->member);
  }

  private function findCurrentTask()
  {
    // Loop through stages, Return on first match
    foreach ($this->stages as $stage => $data) {
      if ($data->required && !$data->completed) {
        $this->currentStage = $stage;
        // Return early
        return;
      }
    }

    $this->currentStage = 'done';
  }

  public function getCurrentTask()
  {
    if (!$this->currentStage) {
      $this->findCurrentTask();
    }

    return $this->currentStage;
  }

  public function isCurrentTask($task)
  {
    return $task == $this->getCurrentTask();
  }

  public function completeTask($task)
  {
    $stages = $this->stages;

    if (!isset($this->stages->$task)) {
      throw new \Exception();
    }

    $stages->$task->completed = true;

    $db = app()->db;
    $update = $db->prepare("UPDATE `onboardingMembers` SET `stages` = ? WHERE `id` = ?");
    $update->execute([
      json_encode($stages),
      $this->id,
    ]);

    $this->stages = $stages;

    if ($this->session->checkMemberTasksComplete()) {
      $this->session->completeTask('member_forms');
    }
  }

  public static function getDefaultStages()
  {
    return [
      'medical_form' => [
        'required' => true,
        'completed' => false,
        'required_locked' => false,
        'metadata' => [],
      ],
      'photography_consent' => [
        'required' => true,
        'completed' => false,
        'required_locked' => false,
        'metadata' => [],
      ],
      'code_of_conduct' => [
        'required' => true,
        'completed' => false,
        'required_locked' => false,
        'metadata' => [],
      ],
    ];
  }

  public static function stagesOrder()
  {
    return [
      'medical_form' => 'Medical form',
      'photography_consent' => 'Photography consent',
      'code_of_conduct' => 'Code of conduct',
    ];
  }
}
