<?php

namespace MembershipFees;

class MembershipFees
{

  private $classes;
  private $user;
  private $partial;

  private function __construct($user, $classes, $partial)
  {
    $this->user = $user;
    $this->classes = $classes;
    $this->partial = $partial;
  }

  public static function getByUser($user, $partial = false)
  {
    $db = app()->db;

    // Get classes (Club Memberships)
    $getClasses = $db->prepare("SELECT DISTINCT `ID` FROM clubMembershipClasses INNER JOIN members ON members.ClubCategory = clubMembershipClasses.ID WHERE members.UserID = ? AND members.Active");
    if ($partial) {
      $getClasses = $db->prepare("SELECT DISTINCT `ID` FROM clubMembershipClasses INNER JOIN members ON members.ClubCategory = clubMembershipClasses.ID WHERE members.UserID = ? AND members.RR AND members.Active");
    }
    $getClasses->execute([
      $user,
    ]);
    $classes = $getClasses->fetchAll(\PDO::FETCH_COLUMN);

    $objects = [];
    foreach ($classes as $class) {
      $objects[] = MembershipFeeClass::get($class, $user, $partial);
    }

    // Get classes (NGB Memberships)
    $getClasses = $db->prepare("SELECT DISTINCT `ID` FROM clubMembershipClasses INNER JOIN members ON members.NGBCategory = clubMembershipClasses.ID WHERE members.NGBCategory IS NOT NULL AND members.UserID = ? AND members.Active");
    if ($partial) {
      $getClasses = $db->prepare("SELECT DISTINCT `ID` FROM clubMembershipClasses INNER JOIN members ON members.NGBCategory = clubMembershipClasses.ID WHERE members.NGBCategory IS NOT NULL AND members.UserID = ? AND members.RR AND members.Active");
    }
    $getClasses->execute([
      $user,
    ]);
    $classes = $getClasses->fetchAll(\PDO::FETCH_COLUMN);

    $ngbObjects = [];
    foreach ($classes as $class) {
      $ngbObjects[] = MembershipFeeClass::get($class, $user, $partial);
    }

    $object = new MembershipFees($user, ['club' => $objects, 'national_governing_body' => $ngbObjects], $partial);
    return $object;
  }

  public function getTotal()
  {
    $total = 0;
    foreach ($this->getClasses() as $class) {
      $total += $class->getTotal();
    }

    return $total;
  }

  public function getFormattedTotal()
  {
    return (string) (\Brick\Math\BigDecimal::of((string) $this->getTotal()))->withPointMovedLeft(2)->toScale(2);
  }

  public function getClasses()
  {
    return array_merge($this->getClubClasses(), $this->getNGBClasses());
  }

  public function getClubClasses() {
    return $this->classes['club'];
  }

  public function getNGBClasses() {
    return $this->classes['national_governing_body'];
  }
}
