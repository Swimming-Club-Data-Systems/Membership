<?php

if (!app()->user->hasPermission('Admin')) halt(404);

$start = new DateTime('first day of January next year', new DateTimeZone('Europe/London'));
$end = new DateTime('last day of December next year', new DateTimeZone('Europe/London'));

$pagetitle = "New Membership Year - Membership Centre";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Memberships</a></li>
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships/years")) ?>">Years</a></li>
        <li class="breadcrumb-item active" aria-current="page">New</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          New Membership Year
        </h1>
        <p class="lead mb-0">
          Create a new membership year
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <main>
        <form class="needs-validation" novalidate method="post">

          <div class="mb-3">
            <label for="name" class="form-label">Membership year name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="<?= htmlspecialchars($start->format('y')) ?> Membership Year" required>
          </div>

          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="start" class="form-label">Membership year start date</label>
                <input type="date" class="form-control" id="start" name="start" placeholder="<?= htmlspecialchars($start->format('Y-m-d')) ?>" value="<?= htmlspecialchars($start->format('Y-m-d')) ?>" required>
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="end" class="form-label">Membership year end date</label>
                <input type="date" class="form-control" id="end" name="end" placeholder="<?= htmlspecialchars($end->format('Y-m-d')) ?>" value="<?= htmlspecialchars($end->format('Y-m-d')) ?>" required>
              </div>
            </div>
          </div>

          <p>
            <button type="submit" class="btn btn-primary">
              Add
            </button>
          </p>
        </form>
      </main>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('js/NeedsValidation.js');
$footer->render();
