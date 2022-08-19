<?php

$db = app()->db;
$tenant = app()->tenant;

// Check user exists
$userInfo = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile, RR FROM users WHERE Tenant = ? AND UserID = ? AND Active");
$userInfo->execute([
  $tenant->getId(),
  $id
]);

$info = $userInfo->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

// Check how many current or upcoming periods there are
$date = new DateTime('now', new DateTimeZone('Europe/London'));
$getYears = $db->prepare("SELECT `ID`, `Name`, `StartDate`, `EndDate` FROM membershipYear WHERE EndDate > ? AND Tenant = ?");
$getYears->execute([
  $date->format('Y-m-d'),
  $tenant->getId(),
]);

$year = $getYears->fetch(PDO::FETCH_OBJ);

if (!$year) {
  //  Nothing to choose
  halt(404);
}

// Show selection page

$pagetitle = 'Select Membership Year';
include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Memberships</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("memberships/years")) ?>">Years</a></li>
        <li class="breadcrumb-item active" aria-current="page">New Batch</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Select a year
        </h1>
        <p class="lead mb-0">
          For <?= htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) ?>'s batch
        </p>
      </div>
      <div class="col-auto ms-lg-auto">
        <a href="<?= htmlspecialchars(autoUrl("users/" . urlencode($id))) ?>" class="btn btn-warning">Cancel</a>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <div class="list-group">
        <?php do { ?>
          <a href="<?= htmlspecialchars(autoUrl("memberships/years/$year->ID/new-batch?user=" . urlencode($id))) ?>" class="list-group-item list-group-item-action">
            <?= htmlspecialchars($year->Name) ?>
          </a>
        <?php } while ($year = $getYears->fetch(PDO::FETCH_OBJ)); ?>
      </div>

    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('js/memberships/new-batch.js');
$footer->render();
