<?php

$db = app()->db;
$tenant = app()->tenant;

$galaDetails = $db->prepare("SELECT GalaName `name`, GalaDate `ends`, CoachEnters FROM galas WHERE GalaID = ? AND Tenant = ?");
$galaDetails->execute([
  $id,
  $tenant->getId()
]);
$gala = $galaDetails->fetch(PDO::FETCH_ASSOC);

if ($gala == null) {
  halt(404);
}

if (!$gala['CoachEnters']) {
  halt(404);
}

$galaDate = new DateTime($gala['ends'], new DateTimeZone('Europe/London'));
$nowDate = new DateTime('now', new DateTimeZone('Europe/London'));

$getSessions = $db->prepare("SELECT `Name`, `ID` FROM galaSessions WHERE Gala = ? ORDER BY `ID` ASC");
$getSessions->execute([$id]);
$sessions = $getSessions->fetchAll(PDO::FETCH_ASSOC);

$getCanAttend = $db->prepare("SELECT `Session`, `CanEnter` FROM galaSessionsCanEnter ca INNER JOIN galaSessions gs ON ca.Session = gs.ID WHERE gs.Gala = ? AND ca.Member = ?");

$getSwimmers = $db->prepare("SELECT MemberID id, MForename fn, MSurname sn FROM members WHERE UserID = ?");
$getSwimmers->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
$swimmer = $getSwimmers->fetch(PDO::FETCH_ASSOC);

$hasSwimmers = true;
if ($swimmer == null) {
  $hasSwimmers = false;
}

$pagetitle = 'Select available sessions for ' . htmlspecialchars($gala['name']);

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= autoUrl("galas") ?>">Galas</a></li>
      <li class="breadcrumb-item"><a href="<?= autoUrl("galas/" . $id) ?>"><?= htmlspecialchars($gala['name']) ?></a></li>
      <li class="breadcrumb-item active" aria-current="page">Sessions</li>
    </ol>
  </nav>
  <div class="row">
    <div class="col-lg-8">
      <h1>Select available sessions at <?= htmlspecialchars($gala['name']) ?></h1>
      <p class="lead">Select sessions your swimmers will be able to swim at.</p>
      <p>Your coaches will use this information to make suggested entries for your swimmers.</p>

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

      <form method="post">

        <?php if (!$hasSwimmers) { ?>

          <div class="alert alert-warning">
            You have no swimmers.
          </div>

        <?php } else { ?>
          <?php if ($sessions == null) { ?>
            <div class="alert alert-danger">
              <p class="mb-0"><strong>You cannot complete this form at this time.</strong></p>
              <p class="mb-0">Please contact your club.</p>
            </div>
          <?php } else { ?>
            <?php do {
              $getCanAttend->execute([$id, $swimmer['id']]);
              $canAtt = $getCanAttend->fetchAll(PDO::FETCH_KEY_PAIR);
              $checked = [];
              for ($i = 0; $i < sizeof($sessions); $i++) {
                if (isset($canAtt[$sessions[$i]['ID']]) && $canAtt[$sessions[$i]['ID']]) {
                  $checked[] = " checked ";
                } else {
                  $checked[] = "";
                }
              }

            ?>
              <h2><?= htmlspecialchars(\SCDS\Formatting\Names::format($swimmer['fn'], $swimmer['sn'])) ?></h2>
              <p class="lead"><?= htmlspecialchars($swimmer['fn']) ?> is able to enter;</p>
              <div class="row">
                <?php for ($i = 0; $i < sizeof($sessions); $i++) { ?>
                  <div class="col-sm-6 col-lg-4 col-xl-3">
                    <div class="mb-3">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="<?= $swimmer['id'] ?>-<?= $sessions[$i]['ID'] ?>" name="<?= $swimmer['id'] ?>-<?= $sessions[$i]['ID'] ?>" <?= $checked[$i] ?>>
                        <label class="form-check-label" for="<?= $swimmer['id'] ?>-<?= $sessions[$i]['ID'] ?>">
                          <?= htmlspecialchars($sessions[$i]['Name']) ?>
                        </label>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              </div>
            <?php } while ($swimmer = $getSwimmers->fetch(PDO::FETCH_ASSOC)); ?>
          <?php } ?>
        <?php } ?>

        <?php if ($hasSwimmers && $sessions != null) { ?>
          <p>
            <button class="btn btn-success" type="submit">
              Save
            </button>
          </p>
        <?php } ?>

      </form>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
