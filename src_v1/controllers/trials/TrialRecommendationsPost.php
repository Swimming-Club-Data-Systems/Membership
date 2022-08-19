<?php

$db = app()->db;
$tenant = app()->tenant;

// Verify swimmer
$query = $db->prepare("SELECT COUNT(*) FROM joinSwimmers WHERE ID = ? AND Tenant = ?");
$query->execute([
  $request,
  $tenant->getId()
]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$squad = $_POST['squad'];

$squad = $_POST['squad'];
if ($squad == "null") {
  $query = $db->prepare("UPDATE joinSwimmers SET Comments = ? WHERE ID = ? AND Tenant = ?");
  $query->execute([
    htmlspecialchars(trim($_POST['comments'])),
    $request,
    $tenant->getId()
  ]);
} else {

  // Verify squad is for this tenant
  $query = $db->prepare("SELECT COUNT(*) FROM squads WHERE SquadID = ? AND Tenant = ?");
  $query->execute([
    $squad,
    $tenant->getId()
  ]);

  if ($query->fetchColumn() == 0) {
    halt(404);
  }

  $query = $db->prepare("UPDATE joinSwimmers SET Comments = ?, SquadSuggestion = ? WHERE ID = ? AND Tenant = ?");
  $query->execute([
    htmlspecialchars(trim($_POST['comments'])), 
    $squad, 
    $request,
    $tenant->getId()
  ]);
}

$_SESSION['TENANT-' . app()->tenant->getId()]['TrialRecommendationsUpdated'] = true;
header("Location: " . currentUrl());
