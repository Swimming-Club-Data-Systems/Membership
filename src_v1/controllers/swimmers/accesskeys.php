<?php
$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];
if ($access != "Admin" && $access != "Coach" && $access != "Galas") {
  halt(404);
}

$db = app()->db;
$tenant = app()->tenant;

$swimmers = $db->prepare("SELECT members.MemberID, members.MForename, members.MSurname, members.ASANumber, members.AccessKey FROM members WHERE members.Tenant = ? ORDER BY `members`.`MForename` , `members`.`MSurname` ASC");
$swimmers->execute([
  $tenant->getId()
]);
$updateASA = $db->prepare("UPDATE `members` SET ASANumber = ? WHERE `MemberID` = ?");
$getSquads = $db->prepare("SELECT SquadName FROM squads INNER JOIN squadMembers ON squadMembers.Squad = squads.SquadID WHERE squadMembers.Member = ?");

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/swimmersMenu.php"; ?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= autoUrl("members") ?>">Members</a></li>
      <li class="breadcrumb-item active" aria-current="page">Access Keys</li>
    </ol>
  </nav>

  <h1>Member Access Keys</h1>
  <p class="lead">See access keys.</p>
  <p><a href="<?= autoUrl("members/access-keys.csv") ?>" class="btn btn-primary" download>Download as CSV</a></p>

  <?php

  if ($row = $swimmers->fetch(PDO::FETCH_ASSOC)) { ?>
    <div class="table-responsive-md">
      <?php if (app('request')->isMobile()) {
      ?><table class="table table-sm table-light"><?php
                                    } else {
                                      ?><table class="table table-hover table-light"><?php
                                                                        } ?>
          <thead>
            <tr>
              <th>Name</th>
              <th>Squads</th>
              <th>Swim England Number</th>
              <th>Access Key</th>
            </tr>
          </thead>
          <tbody>
            <?php do {
              $getSquads->execute([
                $row['MemberID']
              ]);
              $squad = $getSquads->fetch(PDO::FETCH_ASSOC);
            ?>
              <tr>
                <td><?= htmlspecialchars($row['MForename'] . " " . $row['MSurname']) ?></td>
                <td><?php if ($squad) { ?>
                    <ul class="list-unstyled mb-0">
                      <?php do { ?>
                        <li><?= htmlspecialchars($squad['SquadName']) ?></li>
                      <?php } while ($squad = $getSquads->fetch(PDO::FETCH_ASSOC)); ?>
                    </ul>
                  <?php } else { ?>
                    No squads
                  <?php } ?></td>
                <?php if ($row['ASANumber'] == null) {
                  $memID = $row['MemberID'];
                  $asaN = $tenant->getKey('ASA_CLUB_CODE') . $memID;
                ?><td><span class="font-monospace"><?= htmlspecialchars($asaN) ?></span></td><?php
                                                                                    $updateASA->execute([$asaN, $memID]);
                                                                                  } else { ?>
                  <td><span class="font-monospace"><?= htmlspecialchars($row['ASANumber']) ?></span></td>
                <?php } ?>
                <td><samp><?= htmlspecialchars($row['AccessKey']) ?></samp></td>
              </tr>
            <?php } while ($row = $swimmers->fetch(PDO::FETCH_ASSOC)); ?>
          </tbody>
          </table>
    </div>
  <?php } else { ?>
    <div class="alert alert-warning">
      <strong>You have no registered members</strong><br>
      Add a member to get their access keys
    </div>
  <?php } ?>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();

?>