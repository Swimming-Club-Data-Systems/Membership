<?php

/**
 * Class to represent a training session
 */
class TrainingSession
{
  private int $tenant;
  protected int $id;
  protected string $name;
  protected $dayOfWeek;
  protected $startTime;
  protected $endTime;
  protected $displayFrom;
  protected $displayUntil;
  protected $venue;
  protected $squads;

  /**
   * Create an empty squad object
   */
  function __construct()
  {
  }

  /**
   * Returns a squad object by ID
   * 
   * @param id The squad id
   * @return Squad object
   */
  public static function get(int $id)
  {
    $session = new TrainingSession();
    $session->id = $id;
    $session->revalidate();
    return $session;
  }

  /**
   * Returns a list of training sessions
   * 
   * @param Array args
   * @return Array array of training sessions
   */
  public static function list($args = [
    'date' => 'now',
  ])
  {
    $list = [];

    $date = 'now';
    if (isset($args['date'])) {
      $date = $args['date'];
    }
    $today = new DateTime($date, new DateTimeZone('Europe/London'));

    $getSessions = app()->db->prepare("SELECT SessionID FROM sessions WHERE Tenant = :tenant AND DisplayFrom <= :today AND DisplayUntil >= :today AND SessionDay = :sday ORDER BY StartTime ASC, EndTime ASC;");
    $getSessions->execute([
      'tenant' => app()->tenant->getId(),
      'today' => $today->format("Y-m-d"),
      'sday' => $today->format('w'),
    ]);

    while ($session = $getSessions->fetchColumn()) {
      $list[] = TrainingSession::get($session);
    }

    return $list;
  }

  /**
   * Refetch stored information from the database
   */
  public function revalidate()
  {
    $db = app()->db;
    $tenant = app()->tenant;

    $this->tenant = $tenant->getId();

    $getSession = $db->prepare("SELECT SessionName, VenueID, SessionDay, StartTime, EndTime, DisplayFrom, DisplayUntil FROM `sessions` WHERE SessionID = ? AND Tenant = ?");
    $getSession->execute([
      $this->id,
      $this->tenant
    ]);
    $session = $getSession->fetch(PDO::FETCH_ASSOC);

    if ($session == null) {
      throw new Exception('No session');
    }

    $this->venue = TrainingVenue::get($session['VenueID']);

    $this->name = $session['SessionName'];
    $this->dayOfWeek = $session['SessionDay'];
    $this->startTime = $session['StartTime'];
    $this->endTime = $session['EndTime'];
    $this->displayFrom = $session['DisplayFrom'];
    $this->displayUntil = $session['DisplayUntil'];

    // Get squads
    $getSquads = $db->prepare("SELECT `sessionsSquads`.`Squad` FROM `sessionsSquads` INNER JOIN squads ON `sessionsSquads`.`Squad` = squads.SquadID WHERE `Session` = ? ORDER BY SquadFee DESC, SquadName ASC");
    $getSquads->execute([
      $this->id,
    ]);
    $squads = [];
    while ($squad = $getSquads->fetchColumn()) {
      $squads[] = Squad::get($squad);
    }

    $this->squads = $squads;
  }

  /**
   * Get the name of a session
   * 
   * @return string session name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Get the session id
   * 
   * @return int id
   */
  public function getId(): int
  {
    return $this->id;
  }

  /**
   * Get the venue object for the session
   * 
   * @return TrainingVenue the venue
   */
  public function getVenue()
  {
    return $this->venue;
  }

  public function getSquads()
  {
    return $this->squads;
  }

  public function getStartTime()
  {
    return (new DateTime($this->startTime, new DateTimeZone('UTC')));
  }

  public function getEndTime()
  {
    return (new DateTime($this->endTime, new DateTimeZone('UTC')));
  }

  public function getDayOfWeekInt(): int
  {
    return $this->dayOfWeek;
  }

  /**
   * Sets up registers, with members in squad today prepopulated
   * 
   * @param string date string
   */
  public function handleRegisterSetup($dateString = 'now')
  {
    $db = app()->db;

    $weekId = $this->getWeekId($dateString);

    // Check if session exists
    $getRecordCount = $db->prepare("SELECT COUNT(*) FROM `sessionsAttendance` WHERE `WeekID` = ? AND `SessionID` = ?");
    $getRecordCount->execute([
      $weekId,
      $this->id,
    ]);

    // Check this session is not a 'Booking' session
    $bookingDate = new DateTime($dateString, new DateTimeZone('Europe/London'));
    // Check this is not a booking only session!
    $getBookingCount = $db->prepare("SELECT COUNT(*) FROM `sessionsBookable` INNER JOIN `sessions` ON `sessions`.`SessionID` = `sessionsBookable`.`Session` WHERE `sessionsBookable`.`Session` = ? AND `sessionsBookable`.`Date` = ? AND `sessions`.`Tenant` = ?");
    $getBookingCount->execute([
      $this->id,
      $bookingDate->format('Y-m-d'),
      app()->tenant->getId(),
    ]);

    if ($getRecordCount->fetchColumn() == 0 && $getBookingCount->fetchColumn() == 0) {
      // Need to create based on current members

      // Get members
      $getMembers = $db->prepare("SELECT `squadMembers`.`Member`, sessionsSquads.ForAllMembers FROM `sessions` INNER JOIN `sessionsSquads` ON `sessions`.`SessionID` = `sessionsSquads`.`Session` INNER JOIN squadMembers ON sessionsSquads.Squad = squadMembers.Squad WHERE sessions.SessionID = ? ORDER BY sessionsSquads.ForAllMembers DESC");
      $getMembers->execute([
        $this->id,
      ]);

      // Add query
      $addRecord = $db->prepare("INSERT INTO sessionsAttendance (WeekID, SessionID, MemberID, AttendanceBoolean, AttendanceRequired) VALUES (?, ?, ?, ?, ?)");
      $addedMembers = [];

      while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)) {

        if (!isset($addedMembers[$member['Member']])) {
          $addRecord->execute([
            $weekId,
            $this->id,
            $member['Member'],
            0,
            (int) bool($member['ForAllMembers']),
          ]);

          // Prevent double counting of members in multiple squads
          $addedMembers[$member['Member']] = true;
        }
      }
    }
  }

  /**
   * Get register members
   * 
   * @param string date string
   */
  public function getRegister($dateString = 'now')
  {
    $this->handleRegisterSetup($dateString);

    $weekId = $this->getWeekId($dateString);

    $db = app()->db;
    $getMembers = $db->prepare("SELECT MForename fn, MSurname sn, members.MemberID id, members.UserID `uid`, sessionsAttendance.AttendanceBoolean tick, members.OtherNotes notes, members.DateOfBirth dob, members.GenderDisplay show_gender, members.GenderIdentity gender_identity, members.GenderPronouns gender_pronouns FROM sessionsAttendance INNER JOIN members ON members.MemberID = sessionsAttendance.MemberID WHERE sessionsAttendance.WeekID = ? AND sessionsAttendance.SessionID = ? ORDER BY fn ASC, sn ASC;");
    $getMembers->execute([
      $weekId,
      $this->id,
    ]);

    $members = [];

    while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)) {

      // Notes
      $notes = null;
      if (isset($member['notes']) && mb_strlen(trim((string) $member['notes'])) > 0) {
        $notes = trim((string) $member['notes']);
      }

      // Get member medical
      $getMed = $db->prepare("SELECT Conditions, Allergies, Medication FROM memberMedical WHERE MemberID = ?");
      $getMed->execute([
        $member['id'],
      ]);
      $med = $getMed->fetch(PDO::FETCH_ASSOC);
      $medical = [];
      if ($med && isset($med['Conditions']) && mb_strlen(trim((string) $med['Conditions'])) > 0) {
        $medical['Conditions'] = trim((string) $med['Conditions']);
      }
      if ($med && isset($med['Allergies']) && mb_strlen(trim((string) $med['Allergies'])) > 0) {
        $medical['Allergies'] = trim((string) $med['Allergies']);
      }
      if ($med && isset($med['Medication']) && mb_strlen(trim((string) $med['Medication'])) > 0) {
        $medical['Medication'] = trim((string) $med['Medication']);
      }

      // Get photography
      // Calculate age
      $photo = [];

      $dob = new DateTime($member['dob'], new DateTimeZone('Europe/London'));
      $today = new DateTime('now', new DateTimeZone('Europe/London'));

      $age = $dob->diff($today);
      $age = (int) $age->format('%y');

      if ($age < 18) {
        $getPhoto = $db->prepare("SELECT Website, Social, Noticeboard, FilmTraining, ProPhoto FROM memberPhotography WHERE MemberID = ?");
        $getPhoto->execute([
          $member['id'],
        ]);
        $photoDetails = $getPhoto->fetch(PDO::FETCH_ASSOC);

        if ($photoDetails) {
          if (!bool($photoDetails['Website'])) {
            $photo['Website'] = true;
          }
          if (!bool($photoDetails['Social'])) {
            $photo['Social'] = true;
          }
          if (!bool($photoDetails['Noticeboard'])) {
            $photo['Noticeboard'] = true;
          }
          if (!bool($photoDetails['FilmTraining'])) {
            $photo['FilmTraining'] = true;
          }
          if (!bool($photoDetails['ProPhoto'])) {
            $photo['ProPhoto'] = true;
          }
        }
      }

      $emergencyContacts = [];
      try {
        $emergencyContacts = TrainingSession::getEmergencyContacts($member['uid']);
      } catch (Exception $e) {
        // Ignore, stay null
      }

      $tick = $member['tick'] == 1;
      $indeterminate = $member['tick'] == 2;

      $members[] = [
        'id' => $member['id'],
        'fn' => $member['fn'],
        'sn' => $member['sn'],
        'tick' => $tick,
        'indeterminate' => $indeterminate,
        'medical' => $medical,
        'notes' => $notes,
        'photo' => $photo,
        'contacts' => $emergencyContacts,
        'week_id' => $weekId,
        'session_id' => $this->id,
        'user' => $member['uid'],
        'show_gender' => $member['show_gender'],
        'gender_identity' => $member['gender_identity'],
        'gender_pronouns' => $member['gender_pronouns'],
      ];
    }

    return $members;
  }

  /**
   * Get the week id given a date string
   * 
   * @param string date string
   * @return int week id
   */
  public function getWeekId($dateString = 'now')
  {
    $db = app()->db;

    $date = new DateTime($dateString, new DateTimeZone('Europe/London'));

    if ((int) $date->format('N') != '7') {
      $date->modify('last Sunday');
    }

    // Get the week id
    $getWeekId = $db->prepare("SELECT WeekID FROM sessionsWeek WHERE WeekDateBeginning = ? AND Tenant = ?");
    $getWeekId->execute([
      $date->format("Y-m-d"),
      app()->tenant->getId(),
    ]);
    $weekId = $getWeekId->fetchColumn();

    if (!$weekId) {
      throw new Exception('No WeekID');
    }

    return $weekId;
  }

  public static function getEmergencyContacts($user = null, $member = null)
  {
    $contacts = [];

    if ($user == null && $member == null) {
      throw new Exception('Both null');
    }

    $db = app()->db;

    if ($member && !$user) {
      // Get user
      $getUser = $db->prepare("SELECT UserID FROM members WHERE MemberID = ? AND Tenant = ?");
      $getUser->execute([
        $member,
        app()->tenant->getId(),
      ]);
      $user = $getUser->fetchColumn();
    }

    if (!$user) {
      return $contacts;
    }

    if ($user) {
      $getECs = $db->prepare("SELECT Forename, Surname, Mobile FROM users WHERE UserID = ?");
      $getECs->execute([
        $user
      ]);
      $ec = $getECs->fetch(PDO::FETCH_ASSOC);

      if ($ec) {
        $contacts[] = new NewEmergencyContact(
          $ec['Mobile'],
          $ec['Forename'] . ' ' . $ec['Surname'],
          null,
          $user,
          true
        );
      }

      $getECs = $db->prepare("SELECT ID, `Name`, ContactNumber, Relation FROM emergencyContacts WHERE UserID = ?");
      $getECs->execute([
        $user
      ]);
      while ($ec = $getECs->fetch(PDO::FETCH_ASSOC)) {
        $contacts[] = new NewEmergencyContact(
          $ec['ContactNumber'],
          $ec['Name'],
          $ec['Relation'],
          $user
        );
      }
    }

    return $contacts;
  }

  /**
   * Get the week id given a date string
   * 
   * @param string date string
   * @return int week id
   */
  public static function weekId($dateString = 'now')
  {
    $db = app()->db;

    $date = new DateTime($dateString, new DateTimeZone('Europe/London'));

    if ((int) $date->format('N') != '7') {
      $date->modify('last Sunday');
    }

    // Get the week id
    $getWeekId = $db->prepare("SELECT WeekID FROM sessionsWeek WHERE WeekDateBeginning = ? AND Tenant = ?");
    $getWeekId->execute([
      $date->format("Y-m-d"),
      app()->tenant->getId(),
    ]);
    $weekId = $getWeekId->fetchColumn();

    if (!$weekId) {
      throw new Exception('No WeekID');
    }

    return $weekId;
  }
}
