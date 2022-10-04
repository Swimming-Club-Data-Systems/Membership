<?php

$pagetitle = "Squad Reps";

$db = app()->db;
$tenant = app()->tenant;

$members = [];

$getMyMembers = $db->prepare("SELECT members.MemberID, MForename, MSurname FROM members WHERE UserID = ?");
$getMyMembers->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
]);

$getSquads = $db->prepare("SELECT SquadName `name`, SquadID id FROM squads INNER JOIN squadMembers ON squads.SquadID = squadMembers.Squad WHERE squadMembers.Member = ? ORDER BY SquadFee DESC, SquadName ASC");

$getReps = $db->prepare("SELECT Forename forename, Surname surname, ContactDescription contact FROM squadReps INNER JOIN users ON squadReps.User = users.UserID WHERE squadReps.Squad = ?");

$markdown = new ParsedownExtra();
$markdown->setSafeMode(true);

while ($member = $getMyMembers->fetch(PDO::FETCH_ASSOC)) {
  $getSquads->execute([
    $member['MemberID']
  ]);

  $squadsAndReps = [];

  while ($squad = $getSquads->fetch(PDO::FETCH_ASSOC)) {
    $getReps->execute([
      $squad['id']
    ]);

    $reps = $getReps->fetchAll(PDO::FETCH_ASSOC);
    if (sizeof($reps) > 0) {
      $squadsAndReps[] = [
        'name' => $squad['name'],
        'id' => $squad['id'],
        'reps' => $reps
      ];
    }
  }

  $members[] = [
    'forename' => $member['MForename'],
    'surname' => $member['MSurname'],
    'id' => $member['MemberID'],
    'squads' => $squadsAndReps
  ];
}

$getAllReps = $db->prepare("SELECT SquadID, Forename forename, Surname surname, SquadName, ContactDescription contact FROM ((squadReps INNER JOIN squads ON squads.SquadID = squadReps.Squad) INNER JOIN users ON squadReps.User = users.UserID) WHERE squads.Tenant = ? ORDER BY SquadFee DESC, SquadName ASC");
$getAllReps->execute([
  $tenant->getId()
]);
$allReps = $getAllReps->fetchAll(PDO::FETCH_GROUP);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <div class="row">
      <div class="col-lg-8">
        <h1>Squad Reps</h1>
        <p class="lead mb-0">
          Squad reps are here to help
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <?php if (sizeof($members) > 0) { ?>
        <h2>
          Your squad reps
        </h2>
        <p class="lead">
          Reps for each of your member's squads
        </p>

        <?php foreach ($members as $member) { ?>
          <?php foreach ($member['squads'] as $squad) { ?>
            <div class="card mb-3">
              <div class="card-header">
                <h3 class="h6 font-weight-normal mb-0"><strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['forename'], $member['surname'])) ?></strong> <?= htmlspecialchars($squad['name']) ?> Squad Rep<?php if (sizeof($squad) > 1) { ?>s<?php } ?></h3>
              </div>
              <ul class="list-group list-group-flush">
                <?php foreach ($squad['reps'] as $reps) { ?>

                  <li class="list-group-item">
                    <div>
                      <h4 class="h6 mb-0"><?= htmlspecialchars(\SCDS\Formatting\Names::format($reps['forename'], $reps['surname'])) ?></h4>
                    </div>
                    <?php if ($reps['contact']) { ?><div class="">
                        <hr>
                        <h5 class="h6 font-weight-normal">
                          Contact details
                        </h5>
                        <div class="post-content mb-n3"><?= $markdown->parse($reps['contact']) ?></div>
                      </div><?php } ?>

                  </li>
                <?php } ?>
              </ul>
            </div>
          <?php } ?>
        <?php } ?>

      <?php } ?>


      <h2>
        All reps
      </h2>
      <p class="lead">
        Reps for all squads
      </p>

      <?php if (sizeof($allReps) > 0) { ?>
        <?php foreach ($allReps as $squad) { ?>
          <div class="card mb-3">
            <div class="card-header">
              <h3 class="h6 font-weight-normal mb-0"><?= htmlspecialchars($squad[0]['SquadName']) ?> Rep<?php if (sizeof($squad) > 1) { ?>s<?php } ?></h3>
            </div>
            <ul class="list-group list-group-flush">
              <?php foreach ($squad as $reps) { ?>

                <li class="list-group-item">
                  <div>
                    <h4 class="h6 mb-0"><?= htmlspecialchars(\SCDS\Formatting\Names::format($reps['forename'], $reps['surname'])) ?></h4>
                  </div>
                  <?php if ($reps['contact']) { ?><div class="">
                      <hr>
                      <h5 class="h6 font-weight-normal">
                        Contact details
                      </h5>
                      <div class="post-content mb-n3"><?= $markdown->parse($reps['contact']) ?></div>
                    </div><?php } ?>

                </li>
              <?php } ?>
            </ul>
          </div>
        <?php } ?>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>
              There are no squad reps to show
            </strong>
          </p>
          <p class="mb-0">
            Your club either does not have squad reps or has not added them to the system
          </p>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
