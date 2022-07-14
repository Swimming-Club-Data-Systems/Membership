<?php

class Member extends Person
{
  private $db;
  private $tenant;
  // private int $id;
  // private string $forename;
  private $middlenames;
  // private string $surname;
  private $dob;
  private $user;
  private bool $activeMembership;
  private bool $requiresRegistration;
  private string $sex;
  private $notes;
  private $squads;
  private bool $swimEnglandMember;
  private string $swimEnglandNumber;
  private $governingBodyCategory;
  private bool $clubPaysFees;
  private bool $clubPaysSwimEngland;
  private bool $swimEnglandPayingClub;
  private bool $clubMember;
  private string $clubCategory;
  private bool $clubPaysMembership;
  private string $country;
  private bool $current;
  private string $accessKey;
  private bool $showGender;
  private string $genderIdentity;
  private string $genderPronouns;

  /**
   * Create an empty member object
   */
  function __construct($id)
  {
    $this->id = $id;

    $db = app()->db;
    $this->tenant = app()->tenant->getId();

    $getInfo = $db->prepare("SELECT * FROM members WHERE MemberID = ? AND Tenant = ?");
    $getInfo->execute([
      $this->id,
      $this->tenant
    ]);
    $info = $getInfo->fetch(PDO::FETCH_ASSOC);

    if ($info == null) {
      throw new Exception('No member');
    }

    $this->current = bool($info['Active']);

    // Basic info
    $this->forename = $info['MForename'];
    $this->middlenames = $info['MMiddleNames'];
    $this->surname = $info['MSurname'];
    $this->dob = new DateTime($info['DateOfBirth'], new DateTimeZone('Europe/London'));
    $this->user = $info['UserID'];
    $this->sex = $info['Gender'];
    $this->notes = $info['OtherNotes'];

    // Membership info
    $this->activeMembership = bool($info['Status']);
    $this->requiresRegistration = bool($info['RR']);
    $this->requiresRegistrationIsTransfer = bool($info['RRTransfer']);
    $this->clubMember = bool($info['ClubMember']);
    $this->swimEnglandMember = bool($info['ASAMember']);
    $this->swimEnglandNumber = $info['ASANumber'];
    $this->governingBodyCategory = $info['NGBCategory'];
    $this->swimEnglandPayingClub = bool($info['ASAPrimary']);
    $this->clubCategory = $info['ClubCategory'];
    $this->country = $info['Country'];

    // Fees
    $this->clubPaysSwimEngland = bool($info['ASAPaid']);
    $this->clubPaysMembership = bool($info['ClubPaid']);

    // Other
    $this->accessKey = $info['AccessKey'];

    // Gender Identity
    $this->showGender = bool($info['GenderDisplay']);
    if ($this->showGender) {
      $this->genderIdentity = $info['GenderIdentity'];
      $this->genderPronouns = $info['GenderPronouns'];
    }

    // Get squads
    $getSquads = $db->prepare("SELECT Squad FROM squadMembers WHERE Member = ?");
    $getSquads->execute([
      $this->id
    ]);
    $this->squads = $getSquads->fetchAll(PDO::FETCH_COLUMN);
  }

  /**
   * Get member's middlename
   */
  public function getMiddleNames()
  {
    return $this->middlenames;
  }

  /**
   * Get a member's squads
   * 
   * @return Squad[] array of Squad objects
   */
  public function getSquads()
  {
    $squads = [];
    foreach ($this->squads as $squad) {
      $squads[] = Squad::get($squad);
    }
    return $squads;
  }

  /**
   * Get the member age
   * 
   * @return string age in years
   */
  public function getAge($date = 'now')
  {
    $date = new DateTime($date, new DateTimeZone('Europe/London'));
    return $this->dob->diff($date)->y;
  }

  public function getUser()
  {
    if ($this->user) {
      return new User($this->user);
    }
    return null;
  }

  public function getDateOfBirth()
  {
    return clone $this->dob;
  }

  public function getSwimEnglandNumber()
  {
    return $this->swimEnglandNumber;
  }

  public function getGoverningBodyCategoryID()
  {
    return $this->governingBodyCategory;
  }

  public function getGoverningBodyCategoryName()
  {

    if (!$this->getGoverningBodyCategoryID()) {
      return 'NO MEMBERSHIP';
    }

    $db = app()->db;
    $getCatName = $db->prepare("SELECT `Name` FROM `clubMembershipClasses` WHERE `ID` = ? AND `Tenant` = ?");
    $getCatName->execute([
      $this->getGoverningBodyCategoryID(),
      $this->tenant,
    ]);
    $name = $getCatName->fetchColumn();

    if (!$name) {
      throw new \Exception('Missing category');
    }

    return $name;
  }

  public function getCountryCode()
  {
    return $this->country;
  }

  public function getCountry()
  {
    $countries = getISOAlpha2CountriesWithHomeNations();
    return $countries[$this->getCountryCode()];
  }

  public function getMedicalNotes()
  {
    return new MedicalNotes($this->id);
  }

  /**
   * Get the user's emergency contacts
   * 
   * @return NewEmergencyContact[] an array of emergency contacts
   */
  public function getEmergencyContacts()
  {
    $contacts = [];

    if ($this->user) {
      $db = app()->db;
      $getECs = $db->prepare("SELECT Forename, Surname, Mobile FROM users WHERE UserID = ?");
      $getECs->execute([
        $this->user
      ]);
      $ec = $getECs->fetch(PDO::FETCH_ASSOC);

      if ($ec) {
        $contacts[] = new NewEmergencyContact(
          $ec['Mobile'],
          $ec['Forename'] . ' ' . $ec['Surname'],
          null,
          $this->user,
          true
        );
      }

      $getECs = $db->prepare("SELECT ID, `Name`, ContactNumber, Relation FROM emergencyContacts WHERE UserID = ?");
      $getECs->execute([
        $this->user
      ]);
      while ($ec = $getECs->fetch(PDO::FETCH_ASSOC)) {
        $contacts[] = new NewEmergencyContact(
          $ec['ContactNumber'],
          $ec['Name'],
          $ec['Relation'],
          $this->user
        );
      }
    }

    return $contacts;
  }

  /**
   * Is the member's SE fee paid for them
   * 
   * @return bool true if club pays fees
   */
  public function swimEnglandFeesPaid()
  {
    return $this->clubPaysSwimEngland;
  }

  /**
   * Is the member's club membership fee paid
   * 
   * @return bool true if club pays fees
   */
  public function clubMembershipPaid()
  {
    return $this->clubPaysMembership;
  }

  /**
   * Get the members notes
   * 
   * @return string formatted HTML
   */
  public function getNotes() {
    $md = $this->notes;
    $markdown = new \ParsedownExtra();
    $markdown->setSafeMode(true);
    return $markdown->text($md);
  }

  /**
   * Get the members notes as plain text
   * 
   * @return string markdown plain text
   */
  public function getNotesPlainText() {
    return $this->notes;
  }

  public function getPhotoPermissions() {
    $db = app()->db;
    $getPerms = $db->prepare("SELECT Website, Social, Noticeboard, FilmTraining, ProPhoto FROM memberPhotography WHERE MemberID = ?");
    $getPerms->execute([
      $this->id
    ]);
    $perm = $getPerms->fetch(PDO::FETCH_ASSOC);
    $allows = $disallowed = [];
    $cats = [
      'Website' => $this->getForename() . '\'s photograph to be used on the ' . app()->tenant->getName() . ' website.',
      'Social' => $this->getForename() . '\'s photograph to be used on ' . app()->tenant->getName() . ' social media platform/s.',
      'Noticeboard' => $this->getForename() . '\'s photograph to be used within other printed publications such as newspaper articles about ' . app()->tenant->getName() . '.',
      'FilmTraining' => $this->getForename() . ' to be filmed by ' . app()->tenant->getName() . ' for training purposes.',
      'ProPhoto' => $this->getForename() . '\'s photograph to be taken by a professional photographer employed by ' . app()->tenant->getName() . 'as the official photographer at competitions, galas and other organisational events.',
    ];

    foreach ($cats as $cat => $description) {

      $allowed = $perm != null && isset($perm[$cat]) && bool($perm[$cat]);

      $photoPermission = new PhotoPermission(
        $cat,
        $description,
        $allowed
      );

      if ($allowed) {
        $allows[] = $photoPermission;
      } else {
        $disallowed[] = $photoPermission;
      }
    }

    return [
      'allowed' => $allows,
      'disallowed' => $disallowed
    ];
  }

  public function getClubCategory() {
    $db = app()->db;
    $getCat = $db->prepare("SELECT `Name` FROM clubMembershipClasses WHERE ID = ? AND Tenant = ?");
    $getCat->execute([
      $this->clubCategory,
      $this->tenant,
    ]);
    $cat = $getCat->fetchColumn();

    if ($cat) {
      return $cat;
    }
    return 'UNKNOWN';
  }

  public function getSex() {
    return $this->sex;
  }

  public function showGender() {
    return $this->showGender;
  }

  public function getGenderIdentity($override = false) {
    if (!$this->showGender() && !$override) {
      throw new Exception('Gender identity may not be displayed for this member');
    }
    return $this->genderIdentity;
  }

  public function getGenderPronouns($override = false) {
    if (!$this->showGender() && !$override) {
      throw new Exception('Gender pronouns may not be displayed for this member');
    }
    return $this->genderPronouns;
  }
}
