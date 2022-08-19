<?php

$db = app()->db;
$tenant = app()->tenant;

$getGalas = null;
$date = new DateTime('-1 day', new DateTimeZone('Europe/London'));

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
  $getGalas = $db->prepare("SELECT GalaID id, GalaName `name`, GalaVenue venue, GalaDate endDate FROM teamManagers INNER JOIN galas ON teamManagers.Gala = galas.GalaID WHERE teamManagers.User = ? AND galas.GalaDate >= ? AND galas.Tenant = ? ORDER BY GalaDate ASC");
  $getGalas->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
    $date->format("Y-m-d"),
    $tenant->getId()
  ]);
} else {
  $getGalas = $db->prepare("SELECT GalaID id, GalaName `name`, GalaVenue venue, GalaDate endDate FROM galas WHERE galas.GalaDate >= ? AND Tenant = ? ORDER BY GalaDate ASC");
  $getGalas->execute([
    $date->format("Y-m-d"),
    $tenant->getId()
  ]);
}
$gala = $getGalas->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Current galas";
include BASE_PATH . 'views/header.php';

?>

<div class="front-page mb-n3">
  <div class="container-xl">
    <div class="row">
      <div class="col-md-8">
        <h1>Team manager dashboard</h1>
        <p class="lead">Welcome to the team manager dashboard where you can see your current and upcoming galas.</p>
        <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') { ?>
        <p>For data protection reasons, you will lose access to each event after it finishes.</p>
        <?php } ?>
      </div>
    </div>

    <?php if ($gala != null) { ?>
    <h2>Current and upcoming galas</h2>
    <div class="news-grid mb-4">
      <?php do { 
        $galaDay = new DateTime($gala['endDate'], new DateTimeZone('Europe/London')); ?>
        <a href="<?=autoUrl("galas/" . $gala['id'] . "/team-manager")?>">
          <span class="mb-3">
            <span class="title mb-0">
              <?=htmlspecialchars($gala['name'])?>
            </span>
            <span>
              <?=htmlspecialchars($gala['venue'])?>
            </span>
          </span>
          <span class="category">
            <?=htmlspecialchars($galaDay->format("l j F Y"))?>
          </span>
        </a>
      <?php } while ($gala = $getGalas->fetch(PDO::FETCH_ASSOC)); ?>
    </div>
    <?php } else if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') { ?>
    <div class="alert alert-warning">
      <p class="mb-0">
        <strong>You have not been assigned as a team manager for any upcoming galas.</strong>
      </p>
    </div>
    <?php } else { ?>
    <div class="alert alert-warning">
      <p class="mb-0">
        <strong>There are no upcoming galas.</strong>
      </p>
    </div>
    <?php } ?>

  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();