<?php

$db = app()->db;
$tenant = app()->tenant;

use Respect\Validation\Validator as v;

$query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Hash = ? AND Tenant = ?");
$query->execute([
  $hash,
  $tenant->getId()
]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$query = $db->prepare("UPDATE joinParents SET Invited = '1' WHERE Hash = ? AND Tenant = ?");
$query->execute([
  $hash,
  $tenant->getId()
]);

$query = $db->prepare("SELECT First, Last, Email, Hash FROM joinParents WHERE Hash = ? AND Tenant = ?");
$query->execute([
  $hash,
  $tenant->getId()
]);

$parent = $query->fetch(PDO::FETCH_ASSOC);

if (trim($_POST['email-addr']) != $parent['Email'] && v::email()->validate($_POST['email-addr'])) {
  $query = $db->prepare("UPDATE joinParents SET Email = ? WHERE Hash = ?");
  $query->execute([trim($_POST['email-addr']), $hash]);
} else if (!v::email()->validate($_POST['email-addr'])) {
  // cannot send email
  $_SESSION['TENANT-' . app()->tenant->getId()]['EmailInvalid'] = true;
}

$query = $db->prepare("SELECT ID, First, Last, SquadSuggestion, SquadName, SquadFee FROM joinSwimmers INNER JOIN squads ON squads.SquadID = joinSwimmers.SquadSuggestion WHERE Parent = ? AND SquadSuggestion IS NOT NULL AND joinSwimmers.Tenant = ? ORDER BY First ASC, Last ASC");
$query->execute([
  $hash,
  $tenant->getId()
]);

$swimmers = $query->fetchAll(PDO::FETCH_ASSOC);

$email = '
<p>Hello ' . $parent['First'] . ' ' . $parent['Last'] . '</p>';

if (sizeof($swimmers) == 1) {
$email .= '
<p>' . $swimmers[0]['First'] . ' has been offered a place in ' . $swimmers[0]['SquadName'] . ' Squad. The monthly fee for this squad is £' . number_format($swimmers[0]['SquadFee'], 2) . '.</p>';
} else {
$email .= '
<p>Your children have been offered places in the following squads;</p><ul>';
foreach ($swimmers as $swimmer) {
  $email .= '
  <li>' . $swimmer['First'] . ' has been offered a place in ' . $swimmer['SquadName'] . ' Squad. The monthly fee for this squad is £' . number_format($swimmer['SquadFee'], 2) . '</li>';
}
$email .= '
</ul>';
}

if (sizeof($swimmers) > 2) {
  $email .= '
  <p>As you will have three or more swimmers, you are eligible for a discount on your monthly fees. Reply to this email to request what the monthly total would be.</p>';
}

$email .= '
<p>If you wish to complete registration and join the club, <a href="' . autoUrl("register/ac/" . $parent['Hash']) . '">please click here</a>.</p>';

notifySend(null, 'Join ' . app()->tenant->getKey('CLUB_NAME'), $email, $parent['First'] . ' ' . $parent['Last'], $_POST['email-addr']);

header("Location: " . currentUrl());
