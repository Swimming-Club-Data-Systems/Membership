<?php

$db = app()->db;
$tenant = app()->tenant;

$query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Hash = ? AND Tenant = ?");
$query->execute([
  $hash,
  $tenant->getId()
]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$query = $db->prepare("SELECT First, Last FROM joinParents WHERE Hash = ? AND Tenant = ?");
$query->execute([
  $hash,
  $tenant->getId()
]);

$parent = $query->fetch(PDO::FETCH_ASSOC);

$query = $db->prepare("SELECT ID, First, Last, DoB, ASA, Club, XPDetails, XP, Medical, Questions, TrialStart, TrialEnd, SquadSuggestion FROM joinSwimmers WHERE Parent = ? AND Tenant = ? ORDER BY First ASC, Last ASC");
$query->execute([
  $hash,
  $tenant->getId()
]);

$swimmers = $query->fetchAll(PDO::FETCH_ASSOC);

$pagetitle = "Trial Requests - " . $parent['First'] . ' ' . $parent['Last'];
$use_white_background = true;
$use_website_menu = true;
if ($use_membership_menu) {
  $use_website_menu = false;
}

$value = $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-FC'];

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-AddAnother'])) {
  $value = $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-AddAnother'];
}

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <h1>Trial Request Status</h1>
  <div class="row">
    <div class="col-md-10 col-lg-8">
      <p class="lead">
        Hello <?=$parent['First']?>!
      </p>
      <p>
        Below you can view the current status of your trial requests. You can
        also cancel a trial request, which will delete your child's information
        from our systems. If you cancel all trial requests, we'll also delete
        your contact details from our systems. You can always apply again in
        future.
      </p>
      <p>
        We'll be in touch as soon as we can with details about a trial. At busy
        times, this may take a few days.
      </p>

      <?php
      foreach ($swimmers as $swimmer) {

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

        ?>
      <div class="cell">
        <h2><?=$swimmer['First']?></h2>

        <?php if ($swimmer['SquadSuggestion'] == null) { ?>
        <?php if ($swimmer['TrialStart'] != null && $swimmer['TrialStart'] != "" &&
        $swimmer['TrialEnd'] != null && $swimmer['TrialEnd'] != "") { ?>
        <p class="mb-0"><strong>Trial Appointment Time</strong></p>
        <p>
          <?=date("H:i, j F Y", strtotime($swimmer['TrialStart']))?> - <?=date("H:i, j F Y", strtotime($swimmer['TrialEnd']))?>
        </p>
        <?php } else { ?>
        <p>
          We're still waiting to schedule a trial for you. We'll be in touch
          soon.
        </p>
        <?php } ?>
        <?php } else {
        $query = $db->prepare("SELECT SquadName, SquadFee FROM squads WHERE SquadID = ? AND Tenant = ?");
        $query->execute([$swimmer['SquadSuggestion']], $tenant->getId());
        $squad = $query->fetch(PDO::FETCH_ASSOC);?>
        <p>
          <strong>
            Congratulations, <?=$swimmer['First']?> has been offered a squad
            place in <?=$squad['SquadName']?>!
          </strong>
        </p>
        <p>
          <?=$squad['SquadName']?> has a monthly fee of
          &pound;<?=number_format($squad['SquadFee'], 2, '.', ',')?>.
        </p>
        <p>
          If you don't want to take up a place in <?=$squad['SquadName']?>,
          please press <em>Reject Squad Place</em> in the advanced menu to the
          bottom right of this box.
        </p>
        <?php } ?>

        <dl class="row mb-0 pb-0">
          <?php if ($swimmer['ASA'] != null && $swimmer['ASA'] != "") { ?>
          <dt class="col-md-4 col-lg-3">Swim England Number</dt>
          <dd class="col-md-8 col-lg-9">
            <a target="_blank" href="https://www.swimmingresults.org/biogs/biogs_details.php?tiref=<?=$swimmer['ASA']?>">
              <?=$swimmer['ASA']?>
            </a>
          </dd>
          <?php } ?>

          <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin") { ?>
          <dt class="col-md-4 col-lg-3">Date of Birth</dt>
          <dd class="col-md-8 col-lg-9">
            <?=date("j F Y", strtotime($swimmer['DoB']))?>
          </dd>
          <?php } ?>

          <?php if ($swimmer['Club'] != null && $swimmer['Club'] != "") { ?>
          <dt class="col-md-4 col-lg-3">Current/Previous Club</dt>
          <dd class="col-md-8 col-lg-9">
            <?=$swimmer['Club']?>
          </dd>
          <?php } ?>

          <dt class="col-md-4 col-lg-3">Experience</dt>
          <dd class="col-md-8 col-lg-9">
            <?=$exp?>
          </dd>

          <?php if ($swimmer['XPDetails'] != null && $swimmer['XPDetails'] != "") { ?>
          <dt class="col-md-4 col-lg-3">Experience Details</dt>
          <dd class="col-md-8 col-lg-9">
            <?=$swimmer['XPDetails']?>
          </dd>
          <?php } ?>

          <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin") { ?>
          <?php if ($swimmer['Medical'] != null && $swimmer['Medical'] != "") { ?>
          <dt class="col-md-4 col-lg-3">Medical Info</dt>
          <dd class="col-md-8 col-lg-9">
            <?=$swimmer['Medical']?>
          </dd>
          <?php } ?>
          <?php } ?>

          <?php if ($swimmer['Questions'] != null && $swimmer['Questions'] != "") { ?>
          <dt class="col-md-4 col-lg-3">Questions/Comments</dt>
          <dd class="col-md-8 col-lg-9">
            <?=$swimmer['Questions']?>
          </dd>
          <?php } ?>
        </dl>

        <div class="text-end">
          <div class="dropdown">
            <button class="btn btn-danger dropdown-toggle" type="button" id="deleteDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Advanced Options
            </button>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="deleteDropdown">
              <?php if (sizeof($swimmers) > 1) { ?>
              <a href="<?=autoUrl($url_path . $hash . "/cancel/" . $swimmer['ID'])?>" class="dropdown-item">
                <?php if ($swimmer['SquadSuggestion'] == null) { ?>
                Cancel Trial Request
                <?php } else { ?>
                Reject Squad Place
                <?php } ?>
              </a>
              <?php } else { ?>
              <?php if ($swimmer['SquadSuggestion'] != null) { ?>
              Cancel Trial Request
              <?php } ?>

              <a href="<?=autoUrl($url_path . $hash . "/cancel/all")?>" class="dropdown-item">
                <?php if ($swimmer['SquadSuggestion'] != null) { ?>
                Reject Squad Place
                <?php } else { ?>
                Cancel All Requests
                <?php } ?>
              </a>
              <?php } ?>
            </div>
          </div>
        </div>

      </div>
      <?php }
      ?>

      <h2>Don't want to join our club?</h2>
      <p>
        You can cancel all trial requests and delete your details from our
        system. If you have already been given a trial appointment, it will be
        cancelled and our coaches informed.
      </p>
      <p>
        <a href="<?=autoUrl($url_path . $hash . "/cancel/all")?>" class="btn btn-danger">
          Cancel All Requests
        </a>
      </p>

    </div>

  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->render();
