<?php

$db = app()->db;

$query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Hash = ? AND Invited = ?");
$query->execute([$hash, true]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$query = $db->prepare("SELECT First, Last, Email, Hash FROM joinParents WHERE Hash = ?");
$query->execute([$hash]);

$parent = $query->fetch(PDO::FETCH_ASSOC);

$query = $db->prepare("SELECT ID, First, Last, SquadSuggestion, SquadName, SquadFee FROM joinSwimmers INNER JOIN squads ON squads.SquadID = joinSwimmers.SquadSuggestion WHERE Parent = ? AND SquadSuggestion IS NOT NULL ORDER BY First ASC, Last ASC");
$query->execute([$hash]);

$swimmers = $query->fetchAll(PDO::FETCH_ASSOC);

$pagetitle = "Join the club";
$use_white_background = true;

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <h1>Hello <?=$parent['First']?></h1>
  <div class="row">
    <div class="col-sm-10 col-md-8">
      <form method="post">
        <p class="lead">
          It's great that you want to join <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>. There's a few details
          we'll need to get going.
        </p>
        <p>
          You will be registering the following swimmers as members of
          <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> and you'll also be creating a user account for
          yourself. If any swimmers are missing or should not be listed here,
          please contact the membership officer.
        </p>
        <p>
          Your club user account will allow you to control payments by Direct
          Debit, manage medical details, emergency contacts, get email updates
          and make gala entries.
        </p>
        <p>
          If you decide not to join the club, please contact the membership
          officer.
        </p>

        <?php foreach ($swimmers as $s) { ?>
          <div class="cell">
            <h3><?=htmlspecialchars($s['First'])?></h3>
            <p>
              Will join <?=$s['SquadName']?> at
              &pound;<?=number_format($s['SquadFee'], 2)?>
            </p>
          </div>
        <?php } ?>

        <input type="hidden" name="hash" value="<?=$hash?>">
        <input type="hidden" name="go" value="go">

        <p>
          <button class="btn btn-primary" type="submit">
            Begin registration
          </button>
        </p>

      </form>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
