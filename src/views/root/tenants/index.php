<?php

$db = app()->db;
$getClubs = $db->query("SELECT ID, `Name`, Code, Verified FROM tenants ORDER BY `Name` ASC");
$club = $getClubs->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Clubs";

include BASE_PATH . "views/root/header.php";

?>

<div class="container">
  <div class="row justify-content-center py-3">
    <div class="col-lg-8">
      <h1 class="">Clubs</h1>
      <p class="lead">Find your club to get started.</p>

      <?php if ($club) { ?>
      <div class="card">
        <!-- <div class="card-header">
          Featured
        </div> -->
        <div class="list-group list-group-flush">
          <a class="list-group-item list-group-item-action" href="<?=htmlspecialchars(autoUrl('clse'))?>">Chester-le-Street ASC</a>
          <a class="list-group-item list-group-item-action" href="<?=htmlspecialchars(autoUrl('dare'))?>">Darlington ASC</a>
          <a class="list-group-item list-group-item-action" href="<?=htmlspecialchars(autoUrl('newe'))?>">Newcastle Swim Team</a>
          <a class="list-group-item list-group-item-action" href="https://membership.nasc.co.uk/">Northallerton ASC</a>
          <a class="list-group-item list-group-item-action" href="<?=htmlspecialchars(autoUrl('rice'))?>">Richmond Dales ASC</a>
          <!-- <?php do {
            $link = $club['ID'];
            if ($club['Code']) {
              $link = mb_strtolower($club['Code']);
            }
          ?>
          <a class="list-group-item list-group-item-action" href="<?=htmlspecialchars(autoUrl($link))?>"><?=htmlspecialchars($club['Name'])?></a>
          <?php } while ($club = $getClubs->fetch(PDO::FETCH_ASSOC)); ?> -->
          </div>
      </div>
      <?php } else { ?>
      <div class="alert alert-info">
        <p class="mb-0">
          <strong>No clubs</strong>
        </p>
      </div>
      <?php } ?>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\RootFooter();
$footer->render(); ?>