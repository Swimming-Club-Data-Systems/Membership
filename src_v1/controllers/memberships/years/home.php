<?php

if (!app()->user->hasPermission('Admin')) halt(404);

$db = app()->db;
$tenant = app()->tenant;

$getYears = $db->prepare("SELECT `ID`, `Name`, `StartDate`, `EndDate` FROM `membershipYear` WHERE `Tenant` = ? ORDER BY `EndDate` DESC, `StartDate` DESC");
$getYears->execute([
  $tenant->getId(),
]);
$year = $getYears->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Membership Years - Membership Centre";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Memberships</a></li>
        <li class="breadcrumb-item active" aria-current="page">Years</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Membership Years
        </h1>
        <p class="lead mb-3 mb-lg-0">
          Create or view details for a membership year
        </p>
      </div>
      <div class="col-auto ms-lg-auto">
        <a href="<?= htmlspecialchars(autoUrl('memberships/years/new')) ?>" class="btn btn-success">New</a>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <p>
        The Membership Centre lets clubs track which memberships their members hold in a given year.
      </p>

      <?php if ($year) { ?>
        <div class="list-group mb-3">
          <?php do { ?>
            <a class="list-group-item list-group-item-action" href="<?= htmlspecialchars(autoUrl('memberships/years/' . $year['ID'])) ?>"><?= htmlspecialchars($year['Name']) ?></a>
          <?php } while ($year = $getYears->fetch(PDO::FETCH_ASSOC)); ?>
        </div>
      <?php } else { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>There are no years to display</strong>
          </p>
          <p class="mb-0">
            Create a new membership year to get started.
          </p>
        </div>
      <?php } ?>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
