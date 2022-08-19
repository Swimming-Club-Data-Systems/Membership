<?php

$db = app()->db;
$tenant = app()->tenant;

$userInfo = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile FROM users WHERE UserID = ? AND Tenant = ?");
$userInfo->execute([
  $id,
  $tenant->getId()
]);
$info = $userInfo->fetch(PDO::FETCH_ASSOC);

$getLists = $db->prepare("SELECT targetedLists.Name, targetedLists.ID FROM targetedLists WHERE Tenant = ?");
$getLists->execute([
  $tenant->getId()
]);
$list = $getLists->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

$pagetitle = 'Assign targeted list access';

include BASE_PATH . "views/header.php";

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= autoUrl("users") ?>">Users</a></li>
      <li class="breadcrumb-item"><a href="<?= autoUrl("users/" . $id) ?>"><?= htmlspecialchars(mb_substr($info['Forename'], 0, 1, 'utf-8') . mb_substr($info['Surname'], 0, 1, 'utf-8')) ?></a></li>
      <li class="breadcrumb-item"><a href="<?= autoUrl("users/" . $id . "/targeted-lists") ?>">Targeted Lists</a></li>
      <li class="breadcrumb-item active" aria-current="page">Assign</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>
        Assign targeted list permissions to <?= htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) ?>
      </h1>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AssignListError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['AssignListError']) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>
              We were unable to assign sending permissions for that list to <?= htmlspecialchars($info['Forename']) ?>
            </strong>
          </p>
        </div>
      <?php
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['AssignListError']);
      } ?>

      <?php if ($list != null) { ?>
        <form method="post">
          <div class="mb-3">
            <label class="form-label" for="list-select">
              Choose targeted list
            </label>
            <select class="form-select" id="list-select" name="list-select">
              <option selected>Select a targeted list</option>
              <?php do { ?>
                <option value="<?= $list['ID'] ?>">
                  <?= htmlspecialchars($list['Name']) ?>
                </option>
              <?php } while ($list = $getLists->fetch(PDO::FETCH_ASSOC)); ?>
            </select>
          </div>

          <p>
            <button type="submit" class="btn btn-primary">
              Assign list
            </button>
          </p>
        </form>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>
              No lists exist
            </strong>
          </p>
          <p class="mb-0">
            Please <a href="<?= htmlspecialchars(autoUrl("notify/lists")) ?>" class="alert-link">create a new list</a> to continue.
          </p>
        </div>
      <?php } ?>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
