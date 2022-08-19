<?php

$db = app()->db;

$getSquadCount = $db->prepare("SELECT COUNT(*) FROM squads INNER JOIN squadReps ON squads.SquadID = squadReps.Squad AND squadReps.User = ?");
$getSquadCount->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
]);
$count = $getSquadCount->fetchColumn();

$squads = $db->prepare("SELECT squads.SquadName, squads.SquadID, squadReps.ContactDescription FROM squads INNER JOIN squadReps ON squads.SquadID = squadReps.Squad AND squadReps.User = ?");
$squads->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
]);

if ($count == 0) {
  halt(404);
}

$pagetitle = "Rep Contact Details";

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('squad-reps')) ?>">Rep Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Contact Details</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Contact details
        </h1>
        <p class="lead mb-0">
          Manage your contact details for all of your squads.
        </p>
        <div class="mb-3 d-lg-none"></div>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h2>
        About
      </h2>
      <p>
        You're able to provide custom contact information for every squad you are a rep for. Parents and members in those squads can see a list of squad reps and contact information for each rep.
      </p>

      <p>
        If you are a rep for more than one squad, we ask you to provide seperate contact details for every squad. This allows you use squad specific email addresses.
      </p>

      <p>
        Formatting with <a href="https://www.markdownguide.org/basic-syntax/">Markdown</a> is supported.
      </p>

      <form method="post" class="needs-validation" novalidate>
        <h2>
          Squads and details
        </h2>

        <?php while ($squad = $squads->fetch(PDO::FETCH_ASSOC)) { ?>
          <div class="mb-3">
            <label class="form-label" for="<?= htmlspecialchars('field-' . $squad['SquadID']) ?>"><?= htmlspecialchars($squad['SquadName']) ?></label>
            <textarea class="form-control font-monospace" id="<?= htmlspecialchars('field-' . $squad['SquadID']) ?>" name="<?= htmlspecialchars('field-' . $squad['SquadID']) ?>" rows="3" maxlength="255"><?= htmlspecialchars((string) $squad['ContactDescription']) ?></textarea>
            <div class="invalid-feedback">
              Please use no more than 255 characters.
            </div>
          </div>
        <?php } ?>

        <p>
          <button type="submit" class="btn btn-primary">
            Save
          </button>
        </p>

      </form>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
