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

$pagetitle = 'Add sessions to ' . htmlspecialchars($gala['name']);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas") ?>">Galas</a></li>
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas/" . $id) ?>">#<?= htmlspecialchars($id) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Sessions</li>
      </ol>
    </nav>

    <h1>Manage sessions at <?= htmlspecialchars($gala['name']) ?></h1>
    <p class="lead mb-0">Adding sessions allows parents to indicate if a swimmer will be able to enter any given session.</p>

  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['GalaAddedSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['GalaAddedSuccess']) { ?>
        <div class="alert alert-success">
          <p class="mb-0"><strong>We've successfully added this gala</strong></p>
          <p class="mb-0">Please now provide information about sessions at this gala</p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['GalaAddedSuccess']);
      } ?>

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
          This gala has finished. Changes you attempt to make will not be saved.
        </div>
      <?php } ?>

      <p>If you don't add sessions for a gala, members will be unable indicate whether they can attend.</p>

      <form method="post">

        <?php if ($session == null) { ?>
          <div class="mb-3">
            <label class="form-label" for="numSessions">Number of sessions</label>
            <input type="number" class="form-control" id="numSessions" name="numSessions" placeholder="Enter number of sessions" aria-describedby="numSessionsHelp">
            <small id="numSessionsHelp" class="form-text text-muted">We'll try to automatically name sessions. You can change these names in a moment.</small>
          </div>
          <?php } else {
          $i = 1;
          do { ?>
            <div class="row align-items-end mb-3 g-2">
              <div class="col">
                <div class="mb-0">
                  <label class="form-label" for="session-<?= $session['ID'] ?>">Session <?= $i ?> name</label>
                  <input type="text" class="form-control" id="session-<?= $session['ID'] ?>" name="session-<?= $session['ID'] ?>" value="<?= htmlspecialchars($session['Name']) ?>" placeholder="Enter name of session">
                </div>
              </div>
              <div class="col-auto">
                <a href="<?= autoUrl("galas/" . $id . "/sessions/" . $session['ID'] . "/delete") ?>" class="btn btn-danger">
                  Delete
                </a>
              </div>
            </div>
          <?php $i++;
          } while ($session = $getSessions->fetch(PDO::FETCH_ASSOC)); ?>
          <div class="mb-3">
            <label class="form-label" for="newSession">Add a session</label>
            <input type="text" class="form-control" id="newSession" name="newSession" placeholder="Enter name of new session">
          </div>
        <?php } ?>

        <p>
          <button class="btn btn-success" type="submit">
            Go
          </button>
        </p>
      </form>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
