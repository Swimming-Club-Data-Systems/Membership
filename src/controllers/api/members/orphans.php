<?php

$db = app()->db;
$tenant = app()->tenant;

$output = [];

try {

  if (!app()->user->hasPermissions(["Admin", "Coach", "Galas"])) {
    throw new Exception("Unauthorised");
  }

  $getMembers = $db->prepare("SELECT MForename, MSurname, MemberID, DateOfBirth, ASANumber FROM members WHERE Tenant = ? AND UserID IS NULL ORDER BY MForename ASC, MSurname ASC");
  $getMembers->execute([
    $tenant->getId(),
  ]);

  $members = [];

  while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)) {
    $members[] = [
      "id" => (int) $member["MemberID"],
      "first_name" => $member["MForename"],
      "last_name" => $member["MSurname"],
      "date_of_birth" => $member["DateOfBirth"],
      "ngb_id" => $member["ASANumber"],
    ];
  }

  $output = $members;

} catch (Exception $e) {

  $output = [
    "success" => false,
    "message" => $e->getMessage(),
  ];
}

echo json_encode($output);