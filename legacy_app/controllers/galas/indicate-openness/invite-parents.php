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

$squads = $db->query("SELECT SquadName `name`, SquadID `id` FROM squads ORDER BY SquadFee DESC, SquadName ASC;");

$galaDate = new DateTime($gala['ends'], new DateTimeZone('Europe/London'));
$nowDate = new DateTime('now', new DateTimeZone('Europe/London'));

$getSessions = $db->prepare("SELECT `Name`, `ID` FROM galaSessions WHERE Gala = ? ORDER BY `ID` ASC");
$getSessions->execute([$id]);
$session = $getSessions->fetch(PDO::FETCH_ASSOC);

$pagetitle = 'Invite parents to enter ' . htmlspecialchars($gala['name']);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas") ?>">Galas</a></li>
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas/" . $id) ?>"><?= htmlspecialchars($gala['name']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Invite Parents</li>
      </ol>
    </nav>

    <h1>Invite members to enter <?= htmlspecialchars($gala['name']) ?></h1>
    <p class="lead mb-0">Send an email to parents/members letting them know they can enter this gala.</p>
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
        <div class="alert alert-danger">Email not sent</div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStatus']);
      } ?>

      <?php if ($nowDate > $galaDate) { ?>
        <div class="alert alert-warning">
          This gala has finished. You cannot send another invitation email.
        </div>
      <?php } ?>

      <form method="post">

        <!-- Invite parents from following squads -->
        <p>Invite parents of swimmers in;</p>

        <div class="row">
          <?php while ($squad = $squads->fetch(PDO::FETCH_ASSOC)) { ?>
            <div class="col-6 col-sm-6 col-md-4 col-lg-3">
              <div class="mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="squad-<?= $squad['id'] ?>" name="squad-<?= $squad['id'] ?>">
                  <label class="form-check-label" for="squad-<?= $squad['id'] ?>"><?= htmlspecialchars($squad['name']) ?></label>
                </div>
              </div>
            </div>
          <?php } ?>
        </div>

        <p>Where swimmers are at least</p>

        <div class="row">
          <div class="col-6 col-md-4 col-lg-3">
            <div class="mb-3">
              <div class="input-group mb-3">
                <input type="num" min="0" max="150" value="9" class="form-control" placeholder="Age" id="min-age" name="min-age" aria-label="Minimum age of swimmers">
                <span class="input-group-text">years old</span>
              </div>
            </div>
          </div>
        </div>

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
