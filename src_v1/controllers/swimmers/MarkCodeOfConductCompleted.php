<?php

$pagetitle = "Marked Complete";
$type = null;

$db = app()->db;
$tenant = app()->tenant;

$getMove = $db->prepare("SELECT MForename, MSurname, SquadName FROM moves INNER JOIN members ON moves.MemberID = members.MemberID INNER JOIN squads ON moves.SquadID = squads.SquadID WHERE members.Tenant = ? AND moves.MemberID = ? AND moves.SquadID = ?");
$getMove->execute([
  $tenant->getId(),
  $swimmer,
  $squad
]);
$details = $getMove->fetch(PDO::FETCH_ASSOC);

if ($details == null) {
  // Not found
  $getMove = $db->prepare("SELECT MForename, MSurname, SquadName FROM moves INNER JOIN squads ON moves.SquadID = squads.SquadID WHERE squads.Tenant = ? AND moves.MemberID = ? AND moves.SquadID = ?");
  $getMove->execute([
    $tenant->getId(),
    $swimmer,
    $squad
  ]);
  $details = $getMove->fetch(PDO::FETCH_ASSOC);

  if ($details == null) {
    $pagetitle = "Details Not Found";
    $type = "DoesNotExist";
  }

  $pagetitle = htmlspecialchars($details['MForename'] . ' ' . $details['MSurname']) . '\'s Code of Conduct';
  $type = "OutOfMove";
} else {
  $pagetitle = htmlspecialchars($details['MForename'] . ' ' . $details['MSurname']) . '\'s Code of Conduct';
  $type = "InMove";
}


include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

<?php if ($type == 'DoesNotExist') { ?>
  <h1>Old Conduct Form</h1>
  <p class="lead">
    We're unable to mark this code of conduct as being completed.
  </p>
  <p>
    This swimmer is either not moving into the squad that this code of conduct applies to or is not in the squad this code of conduct applies to. They will need to get a new code of conduct for their squad.
  </p>

  <p>
    Sorry for any inconvenience caused.
  </p>

<?php } else { ?>
  <h1><?=htmlspecialchars($details['MForename'] . ' ' . $details['MSurname'])?>'s Code of Conduct</h1>
  <p class="lead">
    We've marked this code of conduct as being completed on <?=date("l j F Y")?>
  </p>

  <p>
    Kind regards,<br>
    The <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> Team.
  </p>

<?php } ?>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
