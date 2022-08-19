<?php


$db = app()->db;
$tenant = app()->tenant;

$codesOfConduct = $db->prepare("SELECT Title, ID FROM posts WHERE Tenant = ? AND`Type` = 'conduct_code' ORDER BY Title ASC");
$codesOfConduct->execute([
  $tenant->getId()
]);

$sql = $db->prepare("SELECT * FROM `squads` WHERE Tenant = ? AND squads.SquadID = ?");
$sql->execute([
  $tenant->getId(),
  $id
]);
$row = $sql->fetch(PDO::FETCH_ASSOC);

$pagetitle = htmlspecialchars($row['SquadName']);

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/squadMenu.php"; ?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("squads") ?>">Squads</a></li>
        <li class="breadcrumb-item"><a href="<?= autoUrl("squads/" . $id) ?>"><?= htmlspecialchars($row['SquadName']) ?></a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Edit <?= htmlspecialchars($row['SquadName']) ?>
        </h1>
        <p class="lead mb-0">
          Change details for this squad
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <div class="">
        <form method="post" class="needs-validation" novalidate>

          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateDatabaseError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['UpdateDatabaseError']) { ?>
            <div class="alert alert-danger">A database error occured. We did not save the changes.</div>
          <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateDatabaseError']);
          } ?>

          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['UpdateSuccess']) { ?>
            <div class="alert alert-success">All changes saved</div>
          <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateSuccess']);
          } ?>

          <div class="mb-3">
            <label class="form-label" for="squadName">Squad Name</label>
            <input type="text" class="form-control" id="squadName" name="squadName" placeholder="Enter Squad Name" value="<?= htmlspecialchars($row['SquadName']) ?>" required>
            <div class="invalid-feedback">
              Please provide a squad name.
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="squadFee" class="form-label">Squad Fee</label>
            <div class="input-group">
              <span class="input-group-text">&pound;</span>
              <input type="number" min="0" step="0.01" class="form-control" id="squadFee" name="squadFee" aria-describedby="squadFeeHelp" placeholder="eg 50.00" value="<?= htmlspecialchars($row['SquadFee']) ?>" required>
              <div class="invalid-feedback">
                Please provide a monthly fee.
              </div>
            </div>
            <small id="squadFeeHelp" class="form-text text-muted">A squad can have a fee of &pound;0.00 if it represents
              a group for non paying members</small>
          </div>
          <div class="mb-3 d-none">
            <label class="form-label" for="squadCoach">Squad Coach</label>
            <input type="text" class="form-control" id="squadCoach" name="squadCoach" placeholder="Enter Squad Coach" value="<?= htmlspecialchars($row['SquadCoach']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label" for="squadTimetable">Squad Timetable</label>
            <input type="text" class="form-control" id="squadTimetable" name="squadTimetable" placeholder="Enter Squad Timetable Address" value="<?= htmlspecialchars($row['SquadTimetable']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label" for="squadCoC">Squad Code of Conduct</label>
            <select class="form-select" id="squadCoC" name="squadCoC" aria-describedby="conductSelectHelpBlock">
              <?php while ($codeDetails = $codesOfConduct->fetch(PDO::FETCH_ASSOC)) { ?>
                <option value="<?= htmlspecialchars($codeDetails['ID']) ?>" <?php if ($row['SquadCoC'] == $codeDetails['ID']) { ?>selected<?php } ?>>
                  <?= htmlspecialchars($codeDetails['Title']) ?>
                </option>
              <?php } ?>
            </select>
            <small id="conductSelectHelpBlock" class="form-text text-muted">
              You can create a code of conduct in the <strong>Posts</strong> section of this system and select it here.
              It will be used in various parts of this system, including when new members sign up and when members
              renew.
            </small>
          </div>
          <div class="alert alert-danger">
            <div class="mb-3 mb-0">
              <label class="form-label" for="squadDeleteDanger"><strong>Danger Zone</strong> <br>Delete this squad by entering this key "<span class="font-monospace"><?= htmlspecialchars($row['SquadKey']) ?></span>" in the box below</label>
              <input type="text" class="form-control font-monospace" id="squadDeleteDanger" name="squadDeleteDanger" placeholder="Enter the key" onselectstart="return false" onpaste="return false;" onCopy="return false" onCut="return false" onDrag="return false" onDrop="return false" autocomplete=off>
            </div>
          </div>
          <p>
            <button class="btn btn-success" type="submit">Update</button>
          </p>
        </form>
      </div>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();

?>