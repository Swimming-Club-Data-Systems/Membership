<?php

$db = app()->db;

$output = [
  "success" => true,
];

try {
  if (!app()->user->hasPermission("Admin")) {
    http_response_code(403);
    throw new Exception("Unauthorised");
  }

  if (!isset($_GET['min_age']) || !isset($_GET['max_age']) || !isset($_GET['age_on_day'])) {
    throw new Exception("Missing arguments");
  }

  $minAge = (int) $_GET['min_age'];
  $maxAge = (int) $_GET['max_age'];

  if ($minAge < 0 || $minAge > 120) {
    throw new Exception("Minimum age is out of range");
  }

  if ($maxAge < 0 || $maxAge > 120) {
    throw new Exception("Maximum age is out of range");
  }

  if ($maxAge - $minAge > 20) {
    throw new Exception("Age range is greater than 20");
  }

  $today = new DateTime("now", new DateTimeZone("Europe/London"));
  $ageOnDay = new DateTime($_GET['age_on_day'], new DateTimeZone("Europe/London"));

  $startDate = clone $ageOnDay;
  $endDate = clone $ageOnDay;

  // Start getting members
  // Sort by age
  // Then split into M/F based on competition sex

  $startDate->sub(new DateInterval("P" . $maxAge . "Y"));
  $endDate->sub(new DateInterval("P" . $minAge . "Y"));

  $getMembers = $db->prepare("SELECT MForename, MSurname, DateOfBirth, Gender, ASANumber, clubMembershipClasses.Name NGBCat, GenderIdentity, GenderPronouns, GenderDisplay FROM members INNER JOIN clubMembershipClasses ON members.NGBCategory = clubMembershipClasses.ID WHERE members.Active AND members.Tenant = ? AND DateOfBirth >= ? AND DateOfBirth <= ? ORDER BY MForename ASC, MSurname ASC");
  $getMembers->execute([
    app()->tenant->getId(),
    $startDate->format("Y-m-d"),
    $endDate->format("Y-m-d"),
  ]);

  $members = [];
  while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)) {
    // Calculate age on day
    $dob = new DateTime($member['DateOfBirth'], new DateTimeZone("Europe/London"));
    $diff = $dob->diff($ageOnDay);
    $memberAgeOnDay = $diff->format("%y");
    $diff = $dob->diff($today);
    $memberAgeToday = $diff->format("%y");

    $memberObj = [
      "first_name" => $member['MForename'],
      "last_name" => $member['MSurname'],
      "date_of_birth" => $member['DateOfBirth'],
      "competition_sex" => $member['Gender'],
      "age_on_day" => $memberAgeOnDay,
      "age_today" => $memberAgeToday,
      "ngb_id" => $member['ASANumber'],
      "ngb_category_name" => $member['NGBCat'],
    ];

    $genderId = ($member['GenderIdentity'] && $member['GenderDisplay']) ? $member['GenderIdentity'] : null;

    $memberObj["gender_identity"] = $genderId;

    $members[$memberAgeOnDay][$member['Gender']][] = $memberObj;
  }

  $memberOutput = [];
  foreach ($members as $age => $data) {
    $male = isset($data['Male']) ? $data['Male'] : null;
    $female = isset($data['Female']) ? $data['Female'] : null;

    $memberOutput[] = [
      'age' => $age,
      'male' => $male,
      'female' => $female,
    ];
  }

  usort($memberOutput, function ($a, $b) {
    return $a['age'] - $b['age'];
  });


  $output['members'] = $memberOutput;
  $output['start_date'] = $startDate->format("Y-m-d");
  $output['end_date'] = $endDate->format("Y-m-d");
  $output['min_age'] = $minAge;
  $output['max_age'] = $maxAge;
} catch (Exception $e) {

  $output = [
    "success" => false,
    "message" => $e->getMessage(),
  ];
}

echo json_encode($output);
