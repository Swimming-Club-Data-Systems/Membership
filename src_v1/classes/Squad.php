<?php

/**
 * Class to represent a squad
 */
class Squad {
  private int $tenant;
  protected int $id;
  private string $name;
  private int $monthlyFee;
  private $timetableUrl;
  private $codeOfConduct;

  /**
   * Create an empty squad object
   */
  function __construct() {
  }

  /**
   * Returns a squad object by ID
   * 
   * @param id The squad id
   * @return Squad object
   */
  public static function get(int $id) {
    $squad = new Squad();
    $squad->id = $id;
    $squad->revalidate();
    return $squad;
  }

  /**
   * Refetch stored information from the database
   */
  public function revalidate() {
    $db = app()->db;
    $tenant = app()->tenant;

    $this->tenant = $tenant->getId();

    $getSquad = $db->prepare("SELECT SquadName, SquadFee, SquadTimetable, SquadCoC FROM squads WHERE SquadID = ? AND Tenant = ?");
    $getSquad->execute([
      $this->id,
      $this->tenant
    ]);
    $squad = $getSquad->fetch(PDO::FETCH_ASSOC);

    if ($squad == null) {
      throw new Exception('No squad');
    }

    $this->name = $squad['SquadName'];
    $this->monthlyFee = \Brick\Math\BigDecimal::of((string) $squad['SquadFee'])->withPointMovedRight(2)->toInt();
    $this->timetableUrl = $squad['SquadTimetable'];
    $this->codeOfConduct = $squad['SquadCoC'];
  }

  /**
   * Get the name of a squad
   * 
   * @return string squad name
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Get the squad id
   * 
   * @return int id
   */
  public function getId() : int {
    return $this->id;
  }

  /**
   * Get the monthly fee for a squad
   * 
   * @param int whether to return the fee as an integer for formatted string, default is int
   * @return int|string squad fee
   */
  public function getFee(bool $int = true) {
    if ($int) {
      return $this->monthlyFee;
    } else {
      return (string) \Brick\Math\BigInteger::of((string) $this->monthlyFee)->toBigDecimal()->withPointMovedLeft(2);
    }
  }

  /**
   * Get the url for the timetable
   * 
   * @return string timetable url
   */
  public function getTimetableUrl() {
    return $this->timetableUrl;
  }

  /**
   * Get the post id of a code of conduct
   * 
   * @return int code of conduct id
   */
  public function getCodeOfConductId() {
    return $this->codeOfConduct;
  }

  /**
   * Get a markdown code of conduct
   * 
   * @return string code of conduct md
   */
  public function getCodeOfConductMarkdown() {
    $db = app()->db;
    $getContent = $db->prepare("SELECT Content FROM posts WHERE ID = ?");
    $getContent->execute([
      $this->codeOfConduct
    ]);

    $coc = $getContent->fetchColumn();

    if ($coc == null || mb_strlen(trim((string) $coc)) == 0) {
      return false;
    }
    return trim((string) $coc);
  }

  /**
   * Get a parsed code of conduct
   * 
   * @return string code of conduct md
   */
  public function getCodeOfConduct() {
    return getPostContent($this->codeOfConduct);
  }

  /**
   * Return an array of Coach objects
   * 
   * @return Coach[] array of coaches
   */
  public function getCoaches() {
    $db = app()->db;
    $getCoaches = $db->prepare("SELECT coaches.User, Forename fn, Surname sn, coaches.Type code FROM coaches INNER JOIN users ON coaches.User = users.UserID WHERE coaches.Squad = ? ORDER BY coaches.Type ASC, Forename ASC, Surname ASC");
    $getCoaches->execute([
      $this->id
    ]);

    $coaches = [];
    while ($coach = $getCoaches->fetch(PDO::FETCH_ASSOC)) {
      $coaches[] = new Coach($coach['User'], $coach['fn'], $coach['sn'], $this->id, $coach['code']);
    }

    return $coaches;
  }

  public function getMembers() {
    $db = app()->db;
    $getMembers = $db->prepare("SELECT MemberID FROM members INNER JOIN squadMembers ON squadMembers.Member = members.MemberID WHERE squadMembers.Squad = ? ORDER BY MForename, MSurname");
    $getMembers->execute([
      $this->id
    ]);

    $members = [];
    while ($member = $getMembers->fetchColumn()) {
      $members[] = new Member($member);
    }

    return $members;
  }
}