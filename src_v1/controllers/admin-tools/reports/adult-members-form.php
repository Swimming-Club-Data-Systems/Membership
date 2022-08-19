<?php

$db = app()->db;
$tenant = app()->tenant;

$pagetitle = "Get adult member list";

$getUsers = $db->prepare("SELECT UserID, Forename, Surname, EmailAddress FROM users WHERE Active AND NOT EmailComms AND Tenant = ? ORDER BY Forename ASC, Surname ASC");
$getUsers->execute([
  $tenant->getId(),
]);

$today = new DateTime('now', new DateTimeZone('Europe/London'));

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin")) ?>">Admin</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin/reports")) ?>">Reports</a></li>
      <li class="breadcrumb-item active" aria-current="page">Adults</li>
    </ol>
  </nav>

  <h1>Get a list of adult members</h1>
  <p class="lead">
    Members who will be adults on a given date
  </p>

  <div class="row">
    <div class="col-lg-8">
      <form method="get" class="needs-validation" novalidate action="<?= htmlspecialchars(autoUrl('admin/reports/adult-members-list')) ?>">

        <div class="mb-3">
          <label for="date" class="form-label">18+ on date</label>
          <input type="date" name="date" id="date" required min="<?= htmlspecialchars($today->format('Y-m-d')) ?>" value="<?= htmlspecialchars($today->format('Y-m-d')) ?>" class="form-control">
        </div>

        <p>
          <button class="btn btn-success" type="submit">
            Get member list
          </button>
        </p>

        <p>
          We will generate a CSV file which your PC will download. You can open this in Excel, Numbers or any other spreadsheet software. If you see strange characters, check the text encoding your software is using.
        </p>

        <p>
          Only publish data to the left of the <code>--GDPR BORDER--</code> column.
        </p>

      </form>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
