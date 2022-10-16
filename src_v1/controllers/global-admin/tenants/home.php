<?php

$db = app()->db;
$getClubs = $db->query("SELECT `UniqueID`, `ID`, `Name`, `Code`, `Verified` FROM tenants ORDER BY `Name` ASC");
$club = $getClubs->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Tenants";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <div class="row justify-content-center py-3">
    <div class="col-lg-8 col-md-10">
      <div class="bg-primary text-white p-4 mb-4 d-inline-block rounded">
        <h1 class="mb-0">Tenants</h1>
        <!-- <p class="mb-0">Find your club to get started.</p> -->
      </div>

      <?php if ($club) { ?>
        <div class="card">
          <!-- <div class="card-header">
          Featured
        </div> -->
          <div class="list-group list-group-flush">
            <?php do { ?>
              <a class="list-group-item list-group-item-action" href="<?= htmlspecialchars(autoUrl("admin/tenants/" . $club['UniqueID'])) ?>"><span><?= htmlspecialchars($club['Name']) ?></span><?php if (bool($club['Verified'])) { ?> <i class="fa fa-check-circle text-primary" aria-hidden="true"></i><?php } ?></a>
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

<?php

$footer = new \SCDS\RootFooter();
$footer->render();
