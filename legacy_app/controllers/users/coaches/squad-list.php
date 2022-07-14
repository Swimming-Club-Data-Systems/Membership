<?php

$db = app()->db;
$tenant = app()->tenant;

$responseData = [
  'status' => 200,
  'squads' => [],
  'squadCount' => 0,
  'squadSelects' => null,
  'canAssign' => false
];
$squads = [];

try {

  $getSquads = $db->prepare("SELECT SquadName squad, SquadID id, `Type` `type` FROM coaches INNER JOIN squads ON squads.SquadID = coaches.Squad WHERE coaches.User = ? ORDER BY SquadFee DESC, SquadName ASC");
  $getSquads->execute([
    $_POST['user']
  ]);

  $i = 0;
  while ($squad = $getSquads->fetch(PDO::FETCH_ASSOC)) {
    $squads[] = [
      'id' => $squad['id'],
      'squad' => $squad['squad'],
      'coachType' => $squad['type'],
      'coachTypeDescription' => coachTypeDescription($squad['type'])
    ];
    $i++;
  }

  $responseData['squads'] = $squads;
  $responseData['squadCount'] = $i;

  $getSquads = $db->prepare("SELECT SquadName, SquadID FROM squads WHERE Tenant = ? AND `SquadID` NOT IN (SELECT Squad FROM coaches WHERE User = ?) ORDER BY SquadFee DESC, SquadName ASC");
  $getSquads->execute([
    $tenant->getId(),
    $_POST['user']
  ]);
  $squads = [];
  $countSquads = 0;
  while ($squad = $getSquads->fetch(PDO::FETCH_ASSOC)) {
    $squads[] = [
      'id' => $squad['SquadID'],
      'name' => $squad['SquadName']
    ];
    $countSquads++;
  }
  $squads = [
    'squads' => $squads,
    'roles' => [
      [
        'code' => 'LEAD_COACH',
        'description' => coachTypeDescription('LEAD_COACH')
      ],
      [
        'code' => 'COACH',
        'description' => coachTypeDescription('COACH')
      ],
      [
        'code' => 'ASSISTANT_COACH',
        'description' => coachTypeDescription('ASSISTANT_COACH')
      ],
      [
        'code' => 'TEACHER',
        'description' => coachTypeDescription('TEACHER')
      ],
      [
        'code' => 'HELPER',
        'description' => coachTypeDescription('HELPER')
      ],
      [
        'code' => 'ADMINISTRATOR',
        'description' => coachTypeDescription('ADMINISTRATOR')
      ]
    ]
  ];
  $responseData['squadSelects'] = json_encode($squads);
  if ($countSquads > 0) {
    $responseData['canAssign'] = true;
  }

} catch (PDOException $e) {

  $responseData['status'] = 500;

} catch (Exception $e) {

  $responseData['status'] = 500;

}

http_response_code(200);
header('content-type: application/json');
echo json_encode($responseData);