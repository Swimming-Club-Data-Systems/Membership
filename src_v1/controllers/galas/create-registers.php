<?php

$db = app()->db;
$tenant = app()->tenant;

$galaDetails = $db->prepare("SELECT GalaName `name`, GalaDate `ends` FROM galas WHERE GalaID = ? AND Tenant = ?");
$galaDetails->execute([
  $id,
  $tenant->getId()
]);
$gala = $galaDetails->fetch(PDO::FETCH_ASSOC);

if ($gala == null) {
  halt(404);
}

$galaDate = new DateTime($gala['ends'], new DateTimeZone('Europe/London'));
$nowDate = new DateTime('now', new DateTimeZone('Europe/London'));

$getSessions = $db->prepare("SELECT `Name`, `ID` FROM galaSessions WHERE Gala = ? ORDER BY `ID` ASC");
$getSessions->execute([$id]);
$session = $getSessions->fetch(PDO::FETCH_ASSOC);

// Get Venues
$getVenues = $db->prepare("SELECT VenueName, VenueID FROM sessionsVenues WHERE Tenant = ? ORDER BY VenueName ASC");
$getVenues->execute([
  $tenant->getId()
]);
$venue = $getVenues->fetch(PDO::FETCH_ASSOC);

$pagetitle = 'Create registers for ' . htmlspecialchars($gala['name']);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas") ?>">Galas</a></li>
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas/" . $id) ?>">#<?= htmlspecialchars($id) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Create Registers</li>
      </ol>
    </nav>

    <h1>Create registers for <?= htmlspecialchars($gala['name']) ?></h1>
    <p class="lead mb-0">X.</p>

  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['SuccessStatus']) && $_SESSION['TENANT-' . app()->tenant->getId()]['SuccessStatus']) { ?>
        <div class="alert alert-success">Saved</div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['SuccessStatus']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStatus']) && $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStatus']) { ?>
        <div class="alert alert-danger">Changes were not saved</div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStatus']);
      } ?>

      <?php if ($nowDate > $galaDate) { ?>
        <div class="alert alert-warning">
          This gala has finished.
        </div>
      <?php } ?>

      <form method="post" class="needs-validation" novalidate>

        <?php if ($session == null) { ?>
          <div class="alert alert-danger">
            <p class="mb-0">
              <strong>There are no sessions for this gala.</strong>
            </p>
            <p class="mb-0">
              Please add sessions first.
            </p>
          </div>
        <?php } else { ?>

          <div class="mb-3">
            <label class="form-label" for="session-venue">Competition Venue</label>
            <select class="form-select add-session-form-reset-input" name="session-venue" id="session-venue" required data-default-value="">
              <option selected disabled value="">Select a Venue</option>
              <?php if ($venue) { ?>
                <?php do { ?>
                  <option value="<?= htmlspecialchars($venue['VenueID']) ?>"><?= htmlspecialchars($venue['VenueName']) ?></option>
                <?php } while ($venue = $getVenues->fetch(PDO::FETCH_ASSOC)); ?>
              <?php } ?>
            </select>
            <div class="invalid-feedback">
              You must select a venue for this session
            </div>
          </div>

          <p>
            We recommend that you create a generic venue for galas.
          </p>

          <?php do { ?>
            <div class="row align-items-end mb-3 g-2">
              <div class="col">
                <div class="mb-0">
                  <label class="form-label" for="session-date-<?= $session['ID'] ?>"><?= htmlspecialchars($session['Name']) ?> Date</label>
                  <input type="date" class="form-control" id="session-date-<?= $session['ID'] ?>" name="session-date-<?= $session['ID'] ?>" value="<?= htmlspecialchars($nowDate->format('Y-m-d')) ?>">
                  <div class="invalid-feedback">Please provide a valid date</div>
                </div>
              </div>
              <div class="col">
                <div class="mb-0">
                  <label class="form-label" for="session-start-<?= $session['ID'] ?>"><?= htmlspecialchars($session['Name']) ?> Start Time</label>
                  <input type="time" class="form-control" id="session-start-<?= $session['ID'] ?>" name="session-start-<?= $session['ID'] ?>" value="<?= htmlspecialchars($nowDate->format('H:i')) ?>">
                  <div class="invalid-feedback">Please provide a valid date</div>
                </div>
              </div>
            </div>
          <?php $i++;
          } while ($session = $getSessions->fetch(PDO::FETCH_ASSOC)); ?>

          <p>
            Sessions will be assumed to be three hours long. Timezone <span class="font-monospace">Europe/London</span>.
          </p>

        <?php } ?>

        <p>
          <button class="btn btn-success" type="submit">
            Create Registers
          </button>
        </p>
      </form>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('js/NeedsValidation.js');
$footer->render();
