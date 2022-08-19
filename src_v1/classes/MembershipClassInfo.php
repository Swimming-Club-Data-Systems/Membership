<?php

class MembershipClassInfo {
  public static function getName($id) {
    $db = app()->db;
    $tenant = app()->tenant;

    $getName = $db->prepare("SELECT `Name` FROM `clubMembershipClasses` WHERE `ID` = ? AND `Tenant` = ?");
    $getName->execute([
      $id,
      $tenant->getId(),
    ]);

    $name = $getName->fetchColumn();

    if (!$name) throw new \Exception('Not found');

    return $name;
  }

  public static function getFee($id) {
    $db = app()->db;
    $tenant = app()->tenant;

    $getFees = $db->prepare("SELECT `Fees` FROM `clubMembershipClasses` WHERE `ID` = ? AND `Tenant` = ?");
    $getFees->execute([
      $id,
      $tenant->getId(),
    ]);

    $fees = $getFees->fetchColumn();

    if (!$fees) throw new \Exception('Not found');

    $json = json_decode($fees);

    if ($json->type == 'PerPerson') {
      return $json->fees[0];
    }

    if (!$fees) throw new \Exception('Additional context required');
  }
}