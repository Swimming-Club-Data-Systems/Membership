<?php

class MembershipFees {

  private $classes;


  private function __contruct($user, $classes) {
    $this->user = $user;
    $this->classes = $classes;
  }

  public static function getByUser($user) {
    $db = app()->db;

    // Get classes
    $getClasses = $db->prepare("SELECT DISTINCT `ID` FROM clubMembershipClasses INNER JOIN members ON members.ClubCategory = clubMembershipClasses.ID WHERE members.UserID = ?");
    $getClasses->execute([
      $user,
    ]);
    $classes = $getClasses->fetchAll(\PDO::FETCH_COLUMN);

    $objects = [];
    foreach ($classes as $class) {
      $objects[] = MembershipFeeClass::get($class, $user);
    }

    $object = new MembershipFees($user, $objects);
    return $object;
  }
}