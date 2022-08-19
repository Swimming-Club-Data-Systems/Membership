<?php

// if (!\SCDS\CSRF::verify()) {
//   halt(404);
// }

$db = app()->db;
$tenant = app()->tenant;

$allowedTypes = ['assign', 'revoke'];

$responseData = [
  'status' => 200,
  'message' => 'No operations have been carried out.',
  'user' => [
    'id' => null,
    'forename' => null,
    'surname' => null,
    'email' => null,
    'squads' => []
  ],
  'squad' => [
    'id' => null,
    'name' => null,
    'coaches' => []
  ]
];

try {

  // Get user details
  $user = $db->prepare("SELECT Forename, Surname, EmailAddress FROM users WHERE UserID = ? AND Tenant = ?");
  $user->execute([
    $_POST['user'],
    $tenant->getId()
  ]);
  $user = $user->fetch(PDO::FETCH_ASSOC);

  if (!in_array($_POST['operation'], $allowedTypes)) {
    $responseData['status'] = 400;
    $responseData['message'] = 'Invalid operation requested.';
    throw new Exception();
  }

  if ($user == null) {
    $responseData['status'] = 404;
    $responseData['message'] = 'No such user exists';
    throw new Exception();
  }

  $userObj = new \User($_POST['user']);

  // Add user info
  $responseData['user']['id'] = (int) $_POST['user'];
  $responseData['user']['forname'] = $user['Forename'];
  $responseData['user']['surname'] = $user['Surname'];
  $responseData['user']['email'] = $user['EmailAddress'];

  // Get squad details
  $squad = $db->prepare("SELECT SquadName FROM squads WHERE SquadID = ? AND Tenant = ?");
  $squad->execute([
    $_POST['squad'],
    $tenant->getId()
  ]);
  $squad = $squad->fetchColumn();

  if ($squad == null) {
    $responseData['status'] = 404;
    $responseData['message'] = 'No such squad exists';
  }

  // Add squad info
  $responseData['squad']['id'] = (int) $_POST['squad'];
  $responseData['squad']['name'] = $squad;

  if (!$userObj->hasPermission('Coach')) {
    // Auto assign or remove coach perms
  }

  // Checks complete
  if ($_POST['operation'] == 'assign') {
    // now add to database
    $insert = $db->prepare("INSERT INTO coaches (`Squad`, `User`, `Type`) VALUES (?, ?, ?)");
    $insert->execute([
      $_POST['squad'],
      $_POST['user'],
      $_POST['role']
    ]);
  } else if ($_POST['operation'] == 'revoke') {
    // now add to database
    $delete = $db->prepare("DELETE FROM coaches WHERE `Squad` = ? AND `User` = ?");
    $delete->execute([
      $_POST['squad'],
      $_POST['user']
    ]);
  }



} catch (PDOException $e) {
  // A serious error occurred
  $responseData['status'] = 500;
  $responseData['message'] = 'A database error occurred.';
} catch (Exception $e) {
  // Generic error caused by breaking out of code
}

// End of logic, return response
http_response_code(200);
header("content-type: application/json");
echo json_encode($responseData);