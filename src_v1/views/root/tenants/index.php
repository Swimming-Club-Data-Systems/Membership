<?php

$db = app()->db;
$getClubs = $db->query("SELECT `ID`, `Name`, `Code`, `Verified`, `Domain`, `UniqueID` FROM tenants WHERE `Verified` ORDER BY `Name` ASC");
$club = $getClubs->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Clubs";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <div class="row justify-content-center py-3">
    <div class="col-lg-8 col-md-10">
    <div class="bg-primary text-white p-4 mb-4 d-inline-block rounded">
      <h1 class="">Clubs</h1>
      <p class="mb-0">Find your club to get started.</p>
      </div>

      <?php if ($club) { ?>
        <div class="card">
          <!-- <div class="card-header">
          Featured
        </div> -->
          <div class="list-group list-group-flush">
            <?php do {
              $link = $club['Domain'];
              if (!$link) {
                $link = $club['UniqueID'] . '.' . getenv('MAIN_DOMAIN');
              }
              $link = 'https://' . $link;
            ?>
              <a class="list-group-item list-group-item-action" href="<?= htmlspecialchars($link) ?>"><?= htmlspecialchars($club['Name']) ?></a>
            <?php } while ($club = $getClubs->fetch(PDO::FETCH_ASSOC)); ?>
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