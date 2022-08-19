<?php

$db = app()->db;
$tenant = app()->tenant;

$getSwimmer = $db->prepare("SELECT MForename, MSurname FROM members WHERE MemberID = ? AND Tenant = ?");
$getSwimmer->execute([
  $swimmer,
  $tenant->getId()
]);
$row = $getSwimmer->fetch(PDO::FETCH_ASSOC);

if ($row == null) {
  halt(404);
}

$pagetitle = htmlspecialchars($row['MForename']) . "'s Selected Sessions";

include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("galas")) ?>">Galas</a></li>
        <li class="breadcrumb-item active" aria-current="page">Enter gala</li>
      </ol>
    </nav>

    <h1 class="mb-0">
      <?= htmlspecialchars($row['MForename']) ?>'s Selected Sessions
    </h1>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-md-8">

      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['SuccessStatus']) { ?>
        <p>
          Your selection has been saved. Use the coach entry system to choose swims.
        </p>
      <?php } else { ?>
        <p>
          An error occurred which meant your choices were not saved.
        </p>
      <?php }
      unset($_SESSION['TENANT-' . app()->tenant->getId()]['SuccessStatus']); ?>

      <div class="cell">
        <h3>Make another entry for <?= htmlspecialchars($row['MForename']) ?></h3>

        <p>Return to the entry form to make another entry for <?= htmlspecialchars($row['MForename']) ?>.</p>

        <p class="mb-0">
          <a href="<?= autoUrl("swimmers/" . $swimmer . "/enter-gala") ?>" class="btn btn-primary">
            Make another entry
          </a>
        </p>
      </div>

      <div class="cell">
        <h3>If you're finished here</h3>

        <p>If you've finished making entries, return to the gala homepage or return to the page for <?= htmlspecialchars($row['MForename']) ?>.</p>

        <p class="mb-0">
          <a href="<?= autoUrl("galas") ?>" class="btn btn-primary">
            Gala home
          </a>
          <a href="<?= autoUrl("swimmers/" . $swimmer) ?>" class="btn btn-primary">
            <?= htmlspecialchars($row['MForename']) ?>'s page
          </a>
        </p>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
