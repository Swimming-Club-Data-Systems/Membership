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

<div class="container">
  <div class="row">
    <div class="col-lg-8">
      <h1>Squad Reps</h1>
      <p class="lead">
        Squad reps are here to help
      </p>

      <?php if (sizeof($members) > 0) { ?>
        <h2>
          Your squad reps
        </h2>
        <p class="lead">
          Reps for each of your swimmers
        </p>

        <ul class="list-group mb-3">
          <?php foreach ($members as $member) { ?>
            <li class="list-group-item">
              <h3>
                <?= htmlspecialchars($member['forename']) ?> <?= htmlspecialchars($member['surname']) ?>
              </h3>

              <?php foreach ($member['squads'] as $squad) { ?>
                <h4>
                  <?= htmlspecialchars($squad['name']) ?>
                </h4>

                <ul class="list-group">
                  <?php foreach ($squad['reps'] as $rep) { ?>
                    <li class="list-group-item">
                      <div>
                        <h5><?= htmlspecialchars($rep['forename'] . ' ' . $rep['surname']) ?></h5>
                      </div>
                      <?php if ($rep['contact']) { ?><div class="">
                          <hr>
                          <h6>
                            Contact details
                          </h6>
                          <div class="post-content mb-n3"><?= $markdown->parse($rep['contact']) ?></div>
                        </div><?php } ?>
                    </li>
                  <?php } ?>
                </ul>

              <?php } ?>
            </li>
          <?php } ?>
        </ul>
      <?php } ?>


      <h2>
        All reps
      </h2>
      <p class="lead">
        Here is a full list of squad reps
      </p>

      <?php if (sizeof($allReps) > 0) { ?>
        <ul class="list-group mb-3">
          <?php foreach ($allReps as $squad) { ?>
            <li class="list-group-item">
              <h3><?= htmlspecialchars($squad[0]['SquadName']) ?> Squad Rep<?php if (sizeof($squad) > 1) { ?>s<?php } ?></h3>
              <?php if (sizeof($squad) > 1) { ?>
                <ul class="list-group">
                <?php } ?>
                <?php foreach ($squad as $reps) { ?>
                  <?php if (sizeof($squad) > 1) { ?>
                    <li class="list-group-item">
                    <?php } ?>
                    <div>
                      <h5><?= htmlspecialchars($reps['forename'] . ' ' . $reps['surname']) ?></h5>
                    </div>
                    <?php if ($reps['contact']) { ?><div class="">
                        <hr>
                        <h6>
                          Contact details
                        </h6>
                        <div class="post-content mb-n3"><?= $markdown->parse($reps['contact']) ?></div>
                      </div><?php } ?>
                    <?php if (sizeof($squad) > 1) { ?>
                    </li>
                  <?php } ?>
                <?php } ?>
                <?php if (sizeof($squad) > 1) { ?>
                </ul>
              <?php } ?>
            </li>
          <?php } ?>
        </ul>
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
