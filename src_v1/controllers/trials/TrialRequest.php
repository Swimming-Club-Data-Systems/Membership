<?php

$db = app()->db;
$tenant = app()->tenant;

$query = $db->prepare("SELECT COUNT(*) FROM joinSwimmers WHERE ID = ? AND Tenant = ?");
$query->execute([
  $request,
  $tenant->getId()
]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$query = $db->prepare("SELECT ID, joinSwimmers.First, joinSwimmers.Last, joinParents.First PFirst, joinParents.Last PLast, DoB, ASA, Club, XP, XPDetails, Medical, Questions, TrialStart, TrialEnd FROM joinSwimmers JOIN joinParents WHERE ID = ? AND Tenant = ? ORDER BY First ASC, Last ASC");
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

$fake_time = false;
if ($swimmer['TrialStart'] == "" || $swimmer['TrialStart'] == null ||
$swimmer['TrialEnd'] == "" || $swimmer['TrialStart'] == null) {
  $swimmer['TrialStart'] = date("Y-m-d") . " 18:00:00";
  $swimmer['TrialEnd'] = date("Y-m-d") . " 18:30:00";
  $fake_time = true;
}

$pagetitle = "Trial Request - " . htmlspecialchars($swimmer['First'] . ' ' . $swimmer['Last']);
$use_white_background = true;

$value = $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-FC'];

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrintial-AddAnother'])) {
  $value = $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-AddAnother'];
}

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <h1 class="mb-4">Trial Request for <?=htmlspecialchars($swimmer['First'] . ' ' . $swimmer['Last'])?></h1>
  <div class="row">
    <div class="col-md-6">

      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['TrialAppointmentUpdated'] === true) { ?>
        <div class="alert alert-success">
          <strong>The appointment time was successfully updated.</strong>
        </div>
      <?php } ?>

      <?php if (!$fake_time && $swimmer['TrialStart'] != null &&
      $swimmer['TrialStart'] != "" && $swimmer['TrialEnd'] != null &&
      $swimmer['TrialEnd'] != "") { ?>
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
        <p class="mb-0">
          Use the form to set a trial appointment. The time shown is the
          default.
        </p>
      </div>
      <?php } ?>

      <form method="post">
        <div class="mb-3">
          <label class="form-label" for="trial-date">Trial Date</label>
          <input type="date" class="form-control" id="trial-date" name="trial-date" value="<?=date("Y-m-d", strtotime($swimmer['TrialStart']))?>">
        </div>

        <div class="row">
          <div class="col">
            <div class="mb-3">
              <label class="form-label" for="trial-start">Trial Start Time</label>
              <input type="time" class="form-control" id="trial-start" name="trial-start" value="<?=date("H:i", strtotime($swimmer['TrialStart']))?>">
            </div>
          </div>

          <div class="col">
            <div class="mb-3">
              <label class="form-label" for="trial-end">Trial End Time</label>
              <input type="time" class="form-control" id="trial-end" name="trial-end" value="<?=date("H:i", strtotime($swimmer['TrialEnd']))?>">
            </div>
          </div>
        </div>

        <p>
          <button type="submit" class="btn btn-success">
            Save Trial Appointment Details
          </button>

          <a href="<?=autoUrl($url_path . $hash . "cancel/" . $swimmer['ID'])?>" class="btn btn-danger">
            Cancel Trial Request
          </a>
        </p>
      </form>
    </div>
    <div class="col">
      <dl class="row mb-0 pb-0">
        <?php if ($swimmer['ASA'] != null && $swimmer['ASA'] != "") { ?>
        <dt class="col-md-4">Swim England Number</dt>
        <dd class="col-md-8">
          <a target="_blank" href="https://www.swimmingresults.org/biogs/biogs_details.php?tiref=<?=htmlspecialchars($swimmer['ASA'])?>">
            <?=htmlspecialchars($swimmer['ASA'])?>
          </a>
        </dd>
        <?php } ?>

        <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin") { ?>
        <dt class="col-md-4">Date of Birth</dt>
        <dd class="col-md-8">
          <?=date("j F Y", strtotime($swimmer['DoB']))?>
        </dd>
        <?php } ?>

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

        <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin") { ?>
        <?php if ($swimmer['Medical'] != null && $swimmer['Medical'] != "") { ?>
        <dt class="col-md-4">Medical Info</dt>
        <dd class="col-md-8">
          <?=htmlspecialchars($swimmer['Medical'])?>
        </dd>
        <?php } ?>
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

unset($_SESSION['TENANT-' . app()->tenant->getId()]['TrialAppointmentUpdated']);
$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->render();
