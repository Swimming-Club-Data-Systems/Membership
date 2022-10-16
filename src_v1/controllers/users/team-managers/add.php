<?php

$db = app()->db;
$tenant = app()->tenant;

$userInfo = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile FROM users WHERE UserID = ? AND Tenant = ?");
$userInfo->execute([
  $id,
  $tenant->getId()
]);
$info = $userInfo->fetch(PDO::FETCH_ASSOC);

$date = new DateTime('-1 day', new DateTimeZone('Europe/London'));
$getGalas = $db->prepare("SELECT GalaName, GalaID FROM galas WHERE Tenant = ? AND GalaDate >= ? ORDER BY GalaDate ASC, GalaName ASC");
$getGalas->execute([
  $tenant->getId(),
  $date->format("Y-m-d")
]);
$gala = $getGalas->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

$pagetitle = htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) . ' Team Manager Options';

include BASE_PATH . "views/header.php";

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= autoUrl("users") ?>">Users</a></li>
      <li class="breadcrumb-item"><a href="<?= autoUrl("users/" . $id) ?>"><?= htmlspecialchars(mb_substr($info['Forename'], 0, 1, 'utf-8') . mb_substr($info['Surname'], 0, 1, 'utf-8')) ?></a></li>
      <li class="breadcrumb-item"><a href="<?= autoUrl("users/" . $id . "/team-manager") ?>">TM Settings</a></li>
      <li class="breadcrumb-item active" aria-current="page">Assign</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>
        Assign a gala to <?= htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) ?>
      </h1>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AssignGalaError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['AssignGalaError']) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>
              We were unable to assign that gala to <?= htmlspecialchars($info['Forename']) ?>
            </strong>
          </p>
        </div>
      <?php
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['AssignGalaError']);
      } ?>

      <?php if ($gala != null) { ?>
        <form method="post">
          <div class="mb-3">
            <label class="form-label" for="gala-select">
              Choose a gala
            </label>
            <select class="form-select" id="gala-select" name="gala-select">
              <option selected>Select a gala</option>
              <?php do { ?>
                <option value="<?= $gala['GalaID'] ?>">
                  <?= htmlspecialchars($gala['GalaName']) ?>
                </option>
              <?php } while ($gala = $getGalas->fetch(PDO::FETCH_ASSOC)); ?>
            </select>
          </div>

          <p>
            <button type="submit" class="btn btn-primary">
              Assign gala
            </button>
          </p>
        </form>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>
              There are no galas to choose from
            </strong>
          </p>
        </div>
      <?php } ?>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
