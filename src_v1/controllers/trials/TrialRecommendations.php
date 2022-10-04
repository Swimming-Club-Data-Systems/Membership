<?php

$db = app()->db;
$tenant = app()->tenant;
$currentUser = app()->user;

$query = $db->prepare("SELECT COUNT(*) FROM joinSwimmers WHERE ID = ? AND Tenant = ?");
$query->execute([
  $request,
  $tenant->getId()
]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$query = $db->prepare("SELECT ID, joinSwimmers.First, joinSwimmers.Last, joinParents.First PFirst, joinParents.Last PLast, DoB, ASA, Club, XPDetails, XP, Medical, Questions, TrialStart, TrialEnd, SquadSuggestion, Comments FROM joinSwimmers JOIN joinParents WHERE ID = ? AND Tenant = ? ORDER BY First ASC, Last ASC");
$query->execute([
  $request,
  $tenant->getId()
]);

$swimmer = $query->fetch(PDO::FETCH_ASSOC);

$exp = "None";
if ($swimmer['XP'] == 2) {
  $exp = "Ducklings (pre stages)";
} else if ($swimmer['XP'] == 3) {
  $exp = "School swimming lessons";
} else if ($swimmer['XP'] == 4) {
  $exp = "ASA/Swim England Learn to Swim Stage 1-7";
} else if ($swimmer['XP'] == 5) {
  $exp = "ASA/Swim England Learn to Swim Stage 8-10";
} else if ($swimmer['XP'] == 6) {
  $exp = "Swimming club";
}

$pagetitle = "Trial Request - " . htmlspecialchars($swimmer['First'] . ' ' . $swimmer['Last']);
$use_white_background = true;

$query = $db->prepare("SELECT SquadID, SquadName FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, SquadName ASC");
$query->execute([
  $tenant->getId()
]);

$value = $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-FC'];

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-AddAnother'])) {
  $value = $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-AddAnother'];
}

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <h1 class="mb-4">Trial Recommendations for <?=htmlspecialchars($swimmer['First'] . ' ' . $swimmer['Last'])?></h1>
  <div class="row">
    <div class="col-sm-6">
      <p class="lead">
        Hello <?=htmlspecialchars($currentUser->getName())?>!
      </p>
      <p>
        From this page you can make a recommendation for a squad for
        <?=$swimmer['First']?> <?=$swimmer['Last']?> and leave comments. You can
        also mark them as being ineligible to join.
      </p>

      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['TrialRecommendationsUpdated'] === true) { ?>
        <div class="alert alert-success">
          <strong>Successfully updated the recommendations</strong>
        </div>
      <?php } ?>

      <form method="post">
        <div class="mb-3">
          <label class="form-label" for="comments">Comments on Swimmer</label>
          <textarea class="form-control" id="comments" name="comments" rows="3"><?=$swimmer['Comments']?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label" for="squad">Recommended Squad</label>
          <select class="form-select" name="squad" id="squad" required>

            <?php if ($swimmer['SquadSuggestion'] == null) { ?>
            <option value="null" selected>Select a squad</option>
            <?php }

            while ($squad = $query->fetch(PDO::FETCH_ASSOC)) {
            $selected = "";
            if ($swimmer['SquadSuggestion'] == $squad['SquadID']) {
            $selected = "selected";
             } ?>
            <option value="<?=$squad['SquadID']?>" <?=$selected?>><?=htmlspecialchars($squad['SquadName'])?></option>
            <?php } ?>
          </select>
        </div>

        <p>
          <button type="submit" class="btn btn-success">
            Save details
          </button>

          <a href="<?=autoUrl($url_path . $hash . "cancel/" . $swimmer['ID'])?>" class="btn btn-danger">
            Mark ineligible
          </a>
        </p>

        <p>
            Press <em>Mark ineligible</em> if the swimmer will not be offered a
            place at <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>.
        </p>
      </form>
    </div>
    <div class="col">

      <?php if ($swimmer['TrialStart'] != null && $swimmer['TrialStart'] != "" &&
      $swimmer['TrialEnd'] != null && $swimmer['TrialEnd'] != "") { ?>
      <div class="alert alert-success">
        <p class="mb-0"><strong>Trial Appointment Time</strong></p>
        <p class="mb-0">
          <?=date("H:i, j F Y", strtotime($swimmer['TrialStart']))?> - <?=date("H:i, j F Y", strtotime($swimmer['TrialEnd']))?>
        </p>
      </div>
      <?php } else { ?>
      <div class="alert alert-warning">
        <p class="mb-0">
          <strong>No trial appointment time has been set</strong>
        </p>
      </div>
      <?php } ?>

      <dl class="row">
        <?php if ($swimmer['ASA'] != null && $swimmer['ASA'] != "") { ?>
        <dt class="col-md-4">Swim England Number</dt>
        <dd class="col-md-8">
          <a target="_blank" href="https://www.swimmingresults.org/biogs/biogs_details.php?tiref=<?=htmlspecialchars($swimmer['ASA'])?>">
            <?=htmlspecialchars($swimmer['ASA'])?>
          </a>
        </dd>
        <?php } ?>

        <dt class="col-md-4">Date of Birth</dt>
        <dd class="col-md-8">
          <?=date("j F Y", strtotime($swimmer['DoB']))?>
        </dd>

        <?php if ($swimmer['Club'] != null && $swimmer['Club'] != "") { ?>
        <dt class="col-md-4">Current/Previous Club</dt>
        <dd class="col-md-8">
          <?=htmlspecialchars($swimmer['Club'])?>
        </dd>
        <?php } ?>

        <dt class="col-md-4">Experience</dt>
        <dd class="col-md-8">
          <?=$exp?>
        </dd>

        <?php if ($swimmer['XPDetails'] != null && $swimmer['XPDetails'] != "") { ?>
        <dt class="col-md-4">Experience Details</dt>
        <dd class="col-md-8">
          <?=htmlspecialchars($swimmer['XPDetails'])?>
        </dd>
        <?php } ?>

        <?php if ($swimmer['Medical'] != null && $swimmer['Medical'] != "") { ?>
        <dt class="col-md-4">Medical Info</dt>
        <dd class="col-md-8">
          <?=htmlspecialchars($swimmer['Medical'])?>
        </dd>
        <?php } ?>

        <?php if ($swimmer['Questions'] != null && $swimmer['Questions'] != "") { ?>
        <dt class="col-md-4">Questions and Comments</dt>
        <dd class="col-md-8">
          <?=htmlspecialchars($swimmer['Questions'])?>
        </dd>
        <?php } ?>
      </dl>
    </div>

  </div>
</div>

<?php

unset($_SESSION['TENANT-' . app()->tenant->getId()]['TrialRecommendationsUpdated']);
$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->render();
