<?php

use function GuzzleHttp\json_encode;

try {
  $db = app()->db;
  $tenant = app()->tenant;

  header("content-type: application/json");

  // Get member
  $member = $db->prepare("SELECT MemberID id FROM members WHERE MemberID = ? AND Tenant = ?");
  $member->execute([
    $id,
    $tenant->getId()
  ]);

  $member = $member->fetch(PDO::FETCH_ASSOC);

  // If member does not exist, return error json and exit
  if (!$member) {
    http_response_code(404);
    echo json_encode([
      'status' => 404,
      'message' => 'Not Found'
    ]);
    return;
  }

  // Need to get current squads, squads with planned moves and squads we can move to
  $member = new Member($id);

  if (!app()->user->hasPermission('Admin') && !app()->user->hasPermission('Coach') && (isset($member) && $member->getUser()->getId() != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) {
    http_response_code(404);
    echo json_encode([
      'status' => 404,
      'message' => 'Not Found'
    ]);
    return;
  }

  // Current
  $currentSquads = $member->getSquads();

  // Move to
  $canMoveTo = $db->prepare("SELECT SquadName `name`, SquadID id FROM squads WHERE Tenant = :tenant AND SquadID NOT IN (SELECT Squad SquadID FROM squadMembers WHERE Member = :member) AND SquadID NOT IN (SELECT Old SquadID FROM squadMoves WHERE Member = :member UNION SELECT New SquadID FROM squadMoves WHERE Member = :member) ORDER BY squads.SquadFee DESC, SquadName ASC");
  $canMoveTo->execute([
    'tenant' => $tenant->getId(),
    'member' => $member->getId()
  ]);

  $current = [];
  $memberPays = $db->prepare("SELECT Paying FROM squadMembers WHERE Squad = ? AND Member = ?");
  foreach ($currentSquads as $squad) {
    $memberPays->execute([
      $squad->getId(),
      $member->getId(),
    ]);
    $pays = bool($memberPays->fetchColumn());
    $current[] = [
      'id' => $squad->getId(),
      'name' => $squad->getName(),
      'url' => autoUrl('squads/' . $squad->getId()),
      'price_string' => $squad->getFee(false),
      'pays' => $pays,
    ];
  }

  $squadsDescLine = $member->getForename() . ' is a member of ' . (new NumberFormatter("en", NumberFormatter::SPELLOUT))->format(sizeof($current)) . ' squad';
  if (sizeof($current) != 1) $squadsDescLine .= 's';
  $squadsDescLine .= '.';

  // Get information about planned moves
  $moves = $db->prepare("SELECT `ID`, `Date`, `Paying`, oldSquad.SquadID OldID, oldSquad.SquadName OldName, newSquad.SquadID `NewID`, newSquad.SquadName `NewName` FROM ((squadMoves LEFT JOIN squads AS oldSquad ON squadMoves.Old = oldSquad.SquadID) LEFT JOIN squads AS newSquad ON squadMoves.New = newSquad.SquadID) WHERE Member = ?");
  $moves->execute([
    $member->getId()
  ]);

  $squadMoves = [];
  while ($move = $moves->fetch(PDO::FETCH_ASSOC)) {
    $newSquad = $oldSquad = null;

    if ($move['OldID']) {
      $oldSquad = [
        'id' => (int) $move['OldID'],
        'name' => $move['OldName'],
        'url' => autoUrl('squads/' . $move['OldID']),
      ];
    }

    if ($move['NewID']) {
      $newSquad = [
        'id' => (int) $move['NewID'],
        'name' => $move['NewName'],
        'url' => autoUrl('squads/' . $move['NewID']),
      ];
    }

    $squadMoves[] = [
      'id' => (int) $move['ID'],
      'date' => $move['Date'],
      'paying' => bool($move['Paying']),
      'from' => $oldSquad,
      'to' => $newSquad
    ];
  }

  echo json_encode([
    'current' => $current,
    'can_join' => $canMoveTo->fetchAll(PDO::FETCH_ASSOC),
    'squads_desc_line' => $squadsDescLine,
    'moves' => $squadMoves,
  ]);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'status' => 500,
    'message' => $e->getMessage()
  ]);
}