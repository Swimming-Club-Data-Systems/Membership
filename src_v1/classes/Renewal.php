<?php

use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

/**
 * Renewal class
 */
class Renewal
{

  private string $id;
  private $renewal = null;
  private int $user;
  private $startedOn;
  private $dueBy;
  private $name;
  private $allowLateCompletion;
  private $members = [];
  private $fees = [];
  private $progress;
  private $latestSave;
  private $complete;
  private $stripePaymentIntent;
  private $directDebitPayment;

  private function __construct($id)
  {
    // Create renewal object
    $this->id = $id;
  }

  public static function createUserRenewal($user, $members = null, $renewalId = null)
  {
    $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();
    $renewal = new Renewal($uuid);
    $renewal->create($user, $members, $renewalId);
    $renewal->setRenewal($renewalId);

    return $renewal;
  }

  private function create($user, $members, $renewalId)
  {
    $db = app()->db;

    $this->user = $user;

    if ($renewalId) {
      // $this->name = 'REN NAME';
    }

    $under18 = [];
    $members = [];

    // Work out members
    if (is_array($members) && sizeof($members) > 0) {
      // Get members from this list
    } else {
      // Get members for this user
      $getMembers = $db->prepare("SELECT MForename fn, MSurname sn, MemberID id FROM members WHERE UserID = ? ORDER BY MForename ASC, MSurname ASC");
      $getMembers->execute([
        $user,
      ]);
      while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)) {
        $this->members[] = [
          'id' => (int) $member['id'],
          'name' => $member['fn'] . ' ' . $member['sn'],
        ];

        $members[] = [
          'id' => (int) $member['id'],
        ];
      }

      // Under 18s
      $date = new DateTime('18 years ago', new DateTimeZone('Europe/London'));
      $getMembers = $db->prepare("SELECT MemberID id FROM members WHERE UserID = ? AND DateOfBirth > ? ORDER BY MForename ASC, MSurname ASC");
      $getMembers->execute([
        $user,
        $date->format('Y-m-d'),
      ]);

      while ($member = $getMembers->fetchColumn()) {
        $under18[] = [
          'id' => (int) $member,
        ];
      }
    }

    $this->revalidateMembers();

    // Sort out dates
    $this->startedOn = new DateTime('now', new DateTimeZone('Europe/London'));
    $this->dueBy = new DateTime('last day of this month', new DateTimeZone('Europe/London'));

    if ($this->startedOn > $this->dueBy) {
      $this->dueBy = new DateTime('last day of next month', new DateTimeZone('Europe/London'));
    }

    $this->allowLateCompletion = false;

    // Work out fees, using members
    $this->fees = [];

    // Progress details
    $this->progress = [
      [
        "object" => "account_review",
        "completed" => false,
      ],
      [
        "object" => "member_review",
        "completed" => false,
      ],
      [
        "object" => "fee_review",
        "completed" => false,
      ],
      [
        "object" => "address_review",
        "completed" => false,
      ],
      [
        "object" => "emergency_contacts",
        "completed" => false,
      ],
      [
        "object" => "medical_forms",
        "completed" => false,
        "members" => $members,
      ],
      [
        "object" => "code_of_conduct",
        "completed" => false,
      ],
      [
        "object" => "data_protection_and_privacy",
        "completed" => false,
      ],
      [
        "object" => "terms_and_conditions",
        "completed" => false,
      ],
      [
        "object" => "photography_permissions",
        "members" => $under18,
        "completed" => false,
      ],
      [
        "object" => "admin_form",
        "completed" => false,
      ],
      [
        "object" => "direct_debit",
        "completed" => false,
      ],
      [
        "object" => "renewal_fee",
        "completed" => false,
      ],
    ];

    // Latest save and complete
    $this->latestSave = null;
    $this->complete = false;
  }

  private function revalidateMembers()
  {
    // Sort member list by name
    usort($this->members, function ($item1, $item2) {
      return $item1['name'] <=> $item2['name'];
    });

    // Fetch latest details from DB
    $getMember = app()->db->prepare("SELECT MForename fn, MSurname sn, UserID FROM members WHERE MemberID = ?");
    for ($i = 0; $i < sizeof($this->members); $i++) {
      $getMember->execute([
        $this->members[$i]['id'],
      ]);

      // Use current name incase changed
      if ($member = $getMember->fetch(PDO::FETCH_ASSOC)) {
        $this->members[$i]['name'] = $member['fn'] . ' ' . $member['sn'];
        $this->members[$i]['current'] = $member['UserID'] != null;
      } else {
        $this->members[$i]['current'] = false;
      }
    }
  }

  public static function getUserRenewal($id)
  {
    $object = new Renewal($id);
    $object->update();
    return $object;
  }

  public function update()
  {
    $db = app()->db;
    $getRenewal = $db->prepare("SELECT renewalPeriods.ID PID, renewalPeriods.Opens, renewalPeriods.Closes, renewalPeriods.Name, renewalPeriods.Year, renewalData.ID, renewalData.User, renewalData.Document, renewalData.PaymentIntent, renewalData.PaymentDD FROM renewalData LEFT JOIN renewalPeriods ON renewalPeriods.ID = renewalData.Renewal WHERE renewalData.ID = ?");
    $getRenewal->execute([
      $this->id,
    ]);
    $renewal = $getRenewal->fetch(PDO::FETCH_ASSOC);

    if (!$renewal) throw new Exception('No renewal');

    $this->name = $renewal['Name'];

    $this->renewal = $renewal['PID'];
    $this->user = $renewal['User'];

    $json = json_decode($renewal['Document'], true);

    // Opens date
    if ($renewal['Opens']) {
      $date = new DateTime($renewal['Opens'], new DateTimeZone('UTC'));
      $date->setTimezone(new DateTimeZone('Europe/London'));
      $this->startedOn = $date;
    } else if ($json['started_on']) {
      $date = new DateTime($json['started_on'], new DateTimeZone('UTC'));
      $date->setTimezone(new DateTimeZone('Europe/London'));
      $this->startedOn = $date;
    }

    // Closes date
    if ($renewal['Closes']) {
      $date = new DateTime($renewal['Closes'], new DateTimeZone('UTC'));
      $date->setTimezone(new DateTimeZone('Europe/London'));
      $this->dueBy = $date;
    } else if ($json['due_by']) {
      $date = new DateTime($json['due_by'], new DateTimeZone('UTC'));
      $date->setTimezone(new DateTimeZone('Europe/London'));
      $this->dueBy = $date;
    }

    $this->allowLateCompletion = $json['allow_late_completion'];
    $this->members = $json['members'];
    $this->revalidateMembers();
    $this->fees = $json['fees'];
    $this->progress = $json['progress'];
    $this->complete = $json['complete'];
    $this->stripePaymentIntent = $renewal['PaymentIntent'];
    $this->directDebitPayment = $renewal['PaymentDD'];
  }

  public function save()
  {
    $started = clone $this->startedOn;
    $started->setTimezone(new DateTimeZone('UTC'));
    $due = clone $this->dueBy;
    $due->setTimezone(new DateTimeZone('UTC'));
    $jsonArray = [
      "id" => $this->id,
      "renewal" => $this->renewal,
      "started_on" => $started->format("c"),
      "due_by" => $due->format("c"),
      "allow_late_completion" => $this->allowLateCompletion,
      "members" => $this->members,
      "fees" => $this->fees,
      "progress" => $this->progress,
      "latest_save" => $this->latestSave,
      "complete" => $this->complete,
    ];
    $json = json_encode($jsonArray);

    // Check if we're updating or adding a new one
    $db = app()->db;
    $getCount = $db->prepare("SELECT COUNT(*) FROM renewalData WHERE ID = ?");
    $getCount->execute([
      $this->id,
    ]);

    if ($getCount->fetchColumn() > 0) {
      // Already exists, update
      throw new Exception('Already in db');
    } else {
      // Add new one
      $add = $db->prepare("INSERT INTO `renewalData` (`ID`, `Renewal`, `User`, `Document`, `PaymentIntent`, `PaymentDD`) VALUES (?, ?, ?, ?, ?, ?)");
      $add->execute([
        $this->id,
        $this->renewal,
        $this->user,
        $json,
        $this->stripePaymentIntent,
        $this->directDebitPayment,
      ]);
    }
    // pre($json);
  }

  private function setRenewal($renewal)
  {
    $this->renewal = $renewal;
  }

  public function getRenewalName()
  {
    if ($this->name) {
      return $this->name;
    }
    return 'Registration';
  }

  public function getTypeName($upcase = true)
  {
    $val = 'Registration';
    if ($this->name) {
      $val = 'Renewal';
    }
    if ($upcase) {
      return $val;
    }
    return mb_strtolower($val);
  }

  public function getId()
  {
    return $this->id;
  }

  public function getRenewal()
  {
    return $this->renewal;
  }

  public function getUser()
  {
    return $this->user;
  }

  public function getMembers()
  {
    return $this->members;
  }

  public function getProgress()
  {
    return $this->progress;
  }

  public function memberIsCurrent(int $id): bool
  {
    // Loop through members
    for ($i = 0; $i < sizeof($this->members); $i++) {
      if ($this->members[$i]['id'] == $id) return bool($this->members[$i]['current']);
    }

    return false;
  }

  public function getMember(int $id)
  {
    // Loop through members
    for ($i = 0; $i < sizeof($this->members); $i++) {
      if ($this->members[$i]['id'] == $id) return $this->members[$i];
    }

    throw new Exception('No section found');
  }

  public function getSection(string $sectionName)
  {
    foreach ($this->progress as $id => $object) {
      if ($object['object'] == $sectionName) return $object;
    }

    throw new Exception('No section found');
  }

  public function setCompletedState(string $objectType, bool $state): void
  {
    try {
      $update = app()->db->prepare("UPDATE renewalData SET Document = JSON_REPLACE(Document, REPLACE(JSON_UNQUOTE(JSON_SEARCH(Document, 'one', ?, '', '$.progress')), '.object', '.completed'), ?) WHERE ID = ?");
      $update->execute([
        $objectType,
        $state,
        $this->id,
      ]);
    } catch (PDOException $e) {
      // Error occurred
      // Throw a more generic error
      throw new Exception('Path did not exist, or another error occurred');
    }
  }
}
