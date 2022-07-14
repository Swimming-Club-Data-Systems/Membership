<?php

use function GuzzleHttp\json_encode;

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

$getMember = $db->prepare("SELECT MemberID, UserID, MForename, MSurname FROM members WHERE MemberID = ? AND Tenant = ?");
$getMember->execute([
  $id,
  $tenant->getId(),
]);

$member = $getMember->fetch(PDO::FETCH_ASSOC);

if (!$member) {
  halt(404);
}

$getCountRep = $db->prepare("SELECT COUNT(*) FROM squadMembers WHERE Member = ? AND Squad IN (SELECT Squad FROM squadReps WHERE User = ?)");
$getCountRep->execute([
  $id,
  $user->getId(),
]);
$rep = $getCountRep->fetchColumn() > 0;

if (!$rep && !$user->hasPermission('Admin') && !$user->hasPermission('Coach') && !$user->hasPermission('Galas')) {
  if ($member['UserID'] != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) {
    halt(404);
  }
}

http_response_code(302);

try {

  $date = new DateTime('now', new DateTimeZone('UTC'));
  $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();

  // Get info
  $jsonArray = [
    'id' => $uuid,
    'date' => $date->format('c'),
    'form' => [
      'confirmed-infection' => [
        'state' => false,
        'notes' => '',
      ],
      'exposure' => [
        'state' => false,
        'notes' => '',
      ],
      'underlying-medical' => [
        'state' => false,
        'notes' => '',
      ],
      'live-with-shielder' => [
        'state' => false,
        'notes' => '',
      ],
      'understand-return' => [
        'state' => false,
        'notes' => '',
      ],
      'able-to-train' => [
        'state' => false,
      ],
      'sought-advice' => [
        'state' => false,
      ],
      'advice-received' => [
        'state' => false,
        'notes' => '',
      ],
    ]
  ];

  foreach ($jsonArray['form'] as $key => $value) {
    if (isset($_POST[$key . '-radio']) && bool($_POST[$key . '-radio'])) {
      $jsonArray['form'][$key]['state'] = true;
    }

    if (isset($jsonArray['form'][$key]['notes']) && isset($_POST[$key . '-more-textarea'])) {
      $jsonArray['form'][$key]['notes'] = trim((string) $_POST[$key . '-more-textarea']);
    }
  }

  $json = json_encode($jsonArray);

  // Add to database
  $add = $db->prepare("INSERT INTO `covidHealthScreen` (`ID`, `Member`, `DateTime`, `OfficerApproval`, `Document`) VALUES (?, ?, ?, ?, ?)");
  $add->execute([
    $uuid,
    $id,
    $date->format('Y-m-d H:i:s'),
    (int) false,
    $json
  ]);

  $_SESSION['CovidHealthSurveySuccess'] = true;
  header('location: ' . autoUrl('covid/health-screening'));
} catch (PDOException $e) {
  throw new Exception('A database error occurred');
} catch (Exception $e) {
  $_SESSION['CovidHealthSurveyError'] = $e->getMessage();
  header('location: ' . autoUrl('covid/health-screening/members/' . $id . '/new-survey'));
}