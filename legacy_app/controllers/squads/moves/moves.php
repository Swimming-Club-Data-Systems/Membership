<?php

$db = app()->db;
$tenant = app()->tenant;

$movingMembers = $db->prepare("SELECT DISTINCT MemberID, MForename, MSurname FROM members INNER JOIN squadMoves ON squadMoves.Member = members.MemberID WHERE members.Tenant = ?");
$movingMembers->execute([
  $tenant->getId()
]);
$member = $movingMembers->fetch(PDO::FETCH_ASSOC);

$getMoves = $db->prepare("SELECT squadMoves.ID, Old.SquadName fromName, Old.SquadID fromId, New.SquadName toName, New.SquadID toId, `Date` `date` FROM ((squadMoves LEFT JOIN squads AS Old ON Old.SquadID = squadMoves.Old) LEFT JOIN squads AS New ON New.SquadID = squadMoves.New) WHERE squadMoves.Member = ?");

$pagetitle = "Squad Moves";
include BASE_PATH . "views/header.php";
include BASE_PATH . "views/squadMenu.php";

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('squads')) ?>">Squads</a></li>
      <li class="breadcrumb-item active" aria-current="page">Squad Moves</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col">
      <h1>Squad Moves</h1>
      <p class="lead">Upcoming Squad Moves (Sorted by Date)</p>
      <p>To make a new squad move, <a href="<?= htmlspecialchars(autoUrl("members")) ?>">select a member</a>.</p>
      <!-- LIST -->
      <?php if ($member) { ?>
        <div class="card">
          <div class="card-header">
            All moves
          </div>
          <ul class="list-group list-group-flush">
            <?php do {
              $getMoves->execute([
                $member['MemberID']
              ]);

            ?>
              <li class="list-group-item">
                <div class="row align-items-center">
                  <div class="col">
                    <p>
                      <strong><a href="<?= autoUrl("members/" . $member['MemberID']) ?>"><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?></a></strong>
                    </p>

                    <ul class="mb-0 list-unstyled">
                      <?php while ($move = $getMoves->fetch(PDO::FETCH_ASSOC)) {
                        $moveDate = new DateTime($move['date'], new DateTimeZone('Europe/London')); ?>
                        <li class="py-1">
                          <div class="row align-items-center">
                            <div class="col">
                              <!-- <p class="mb-0"> -->
                              <?php if ($move['fromId'] && $move['toId']) { ?><span class="visually-hidden">Moving from</span> <a class="font-weight-bold" href="<?= autoUrl("squads/" . $move['fromId']) ?>"><?= htmlspecialchars($move['fromName']) ?></a> <i class="fa fa-long-arrow-right" aria-hidden="true"></i><span class="visually-hidden">to</span> <a class="font-weight-bold" href="<?= autoUrl("squads/" . $move['toId']) ?>"><?= htmlspecialchars($move['toName']) ?></a><?php } else if ($move['fromId']) { ?>Leaving <a class="font-weight-bold" href="<?= autoUrl("squads/" . $move['fromId']) ?>"><?= htmlspecialchars($move['fromName']) ?></a><?php } else if ($move['toId']) { ?>Joining <a class="font-weight-bold" href="<?= autoUrl("squads/" . $move['toId']) ?>"><?= htmlspecialchars($move['toName']) ?></a><?php } ?> on <?= htmlspecialchars($moveDate->format("j F Y")) ?>
                              <!-- </p> -->
                            </div>
                            <div class="col-auto">
                              <div class="d-grid gap-2">
                                <a class="btn btn-outline-primary" href="<?= htmlspecialchars(autoUrl("members/" . $member['MemberID'] . '#squads')) ?>">
                                  Edit or Cancel
                                </a>
                              </div>
                            </div>
                          </div>
                        </li>
                      <?php } ?>
                    </ul>
                    <div class="d-lg-none mb-3"></div>
                  </div>
                </div>

              </li>
            <?php } while ($member = $movingMembers->fetch(PDO::FETCH_ASSOC)); ?>
          </ul>
        </div>
      <?php } else { ?>
        <div class="alert alert-warning">
          <strong>There are no upcoming squad moves</strong> <br>
          Check back regularly to see which swimmers may be moving into your squad
        </div>
      <?php } ?>
    </div>
  </div>
</div>
<?php $footer = new \SCDS\Footer();
$footer->render();
