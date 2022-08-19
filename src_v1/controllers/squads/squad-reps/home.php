<?php

$pagetitle = "Squad Rep Home";

$db = app()->db;
$tenant = app()->tenant;

$today = (new DateTime('now', new DateTimeZone('Europe/London')))->format("y-m-d");
$getGalas = $db->prepare("SELECT GalaName, GalaID, GalaVenue FROM galas WHERE GalaDate >= ? AND Tenant = ? ORDER BY GalaDate ASC");
$getGalas->execute([
  $today,
  $tenant->getId()
]);
$gala = $getGalas->fetch(PDO::FETCH_ASSOC);

$squads = null;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
  $squads = $db->prepare("SELECT SquadName, SquadID, SquadCoach FROM squadReps INNER JOIN squads ON squadReps.Squad = squads.SquadID WHERE squadReps.User = ? ORDER BY squads.SquadFee DESC, squads.SquadName ASC");
  $squads->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
} else {
  $squads = $db->prepare("SELECT SquadName, SquadID, SquadCoach FROM squads WHERE Tenant = ? ORDER BY squads.SquadFee DESC, squads.SquadName ASC");
  $squads->execute([
    $tenant->getId()
  ]);
}
$squad = $squads->fetch(PDO::FETCH_ASSOC);

include BASE_PATH . 'views/header.php';

?>

<div class="front-page mb-n3">
  <div class="container-xl">
    <div class="row">
      <div class="col-lg-12">
        <h1>Welcome to Squad Rep Services</h1>
        <p class="lead">This service allows you to view gala entries and their payment status for your squads.</p>

        <div class="mb-4">
          <h2 class="mb-3">
            Your squads
          </h2>
          <?php if ($squad != null) { ?>
            <div class="news-grid">
              <?php do { ?>
                <a href="<?= autoUrl("squads/" . $squad['SquadID']) ?>">
                  <span class="mb-3">
                    <span class="title mb-0">
                      <?= htmlspecialchars($squad['SquadName']) ?>
                    </span>
                    <span>
                      <?= htmlspecialchars($squad['SquadCoach']) ?>
                    </span>
                  </span>
                  <span class="category">
                    Squads
                  </span>
                </a>
              <?php } while ($squad = $squads->fetch(PDO::FETCH_ASSOC)); ?>
            </div>
          <?php } else { ?>
            <div class="alert alert-warning">
              <p class="mb-0">
                <strong>
                  You have no squads to view right now
                </strong>
              </p>
              <p class="mb-0">
                Please check back later
              </p>
            </div>
          <?php } ?>
        </div>

        <div class="mb-4">
          <h2 class="mb-3">
            Upcoming galas
          </h2>
          <?php if ($gala != null) { ?>
            <div class="news-grid">
              <?php do { ?>
                <a href="<?= autoUrl("galas/" . $gala['GalaID'] . "/squad-rep-view") ?>">
                  <span class="mb-3">
                    <span class="title mb-0">
                      <?= htmlspecialchars($gala['GalaName']) ?>
                    </span>
                    <span>
                      <?= htmlspecialchars($gala['GalaVenue']) ?>
                    </span>
                  </span>
                  <span class="category">
                    Galas
                  </span>
                </a>
              <?php } while ($gala = $getGalas->fetch(PDO::FETCH_ASSOC)); ?>
            </div>
          <?php } else { ?>
            <div class="alert alert-warning">
              <p class="mb-0">
                <strong>
                  There are no upcoming galas
                </strong>
              </p>
              <p class="mb-0">
                Please check back later
              </p>
            </div>
          <?php } ?>
        </div>

        <div class="mb-4">
          <h2 class="mb-3">
            Other services
          </h2>
          <div class="news-grid">
            <a href="<?= autoUrl("notify/newemail") ?>">
              <span class="mb-3">
                <span class="title mb-0">
                  Email parents
                </span>
                <span>
                  Email parents of swimmers in your squads
                </span>
              </span>
              <span class="category">
                Notify
              </span>
            </a>
            <a href="<?= autoUrl("contact-tracing/check-in") ?>">
              <span class="mb-3">
                <span class="title mb-0">
                  COVID Liason Squad Registers
                </span>
                <span>
                  Check in members for contact tracing
                </span>
              </span>
              <span class="category">
                Contact Tracing
              </span>
            </a>
            <a href="<?= autoUrl("squad-reps/contact-details") ?>">
              <span class="mb-3">
                <span class="title mb-0">
                  Contact details
                </span>
                <span>
                  Provide contact information to be displayed to parents and members
                </span>
              </span>
              <span class="category">
                Squad Reps
              </span>
            </a>
            <a href="<?= autoUrl("squad-reps/list") ?>">
              <span class="mb-3">
                <span class="title mb-0">
                  View all squads reps
                </span>
                <span>
                  View a list of all squad reps
                </span>
              </span>
              <span class="category">
                Squad Reps
              </span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
