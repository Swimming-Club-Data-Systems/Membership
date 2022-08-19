<?php

use function GuzzleHttp\json_decode;

$db = app()->db;
$tenant = app()->tenant;

$user = app()->user;
if (!$user->hasPermissions(['Admin'])) halt(404);

$getQualifications = $db->prepare("SELECT `ID`, `Name`, `Description`, `DefaultExpiry` FROM `qualifications` WHERE `Show` AND `Tenant` = ?");
$getQualifications->execute([
  $tenant->getId(),
]);
$qualification = $getQualifications->fetch(PDO::FETCH_ASSOC);

$pagetitle = 'Qualifications';

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">

  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Qualifications</li>
      </ol>
    </nav>

    <h1>Qualifications</h1>
    <p class="lead mb-0">
      Track member qualifications
    </p>
  </div>

</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <?php if ($qualification) { ?>
        <div class="list-group mb-3">
          <?php do { ?>
            <a href="<?= htmlspecialchars(autoUrl("qualifications/" . $qualification['ID'])) ?>" class="list-group-item list-group-item-action">
              <?= htmlspecialchars($qualification['Name']) ?>
            </a>
          <?php } while ($qualification = $getQualifications->fetch(PDO::FETCH_ASSOC)); ?>
        </div>
      <?php } else { ?>

      <div class="alert alert-warning">
        <p class="mb-0">
          <strong>There are no qualifications available for members</strong>
        </p>
        <p class="mb-0">
          Add a qualification type first
        </p>
      </div>

      <?php } ?>

      <p>
        <a href="<?= htmlspecialchars(autoUrl('qualifications/new')) ?>" class="btn btn-success">
          Add new qualification type
        </a>
      </p>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
