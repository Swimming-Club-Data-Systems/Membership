<?php

$db = app()->db;
$tenant = app()->tenant;

$userInfo = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile FROM users WHERE UserID = ? AND Tenant = ?");
$userInfo->execute([
  $id,
  $tenant->getId()
]);
$info = $userInfo->fetch(PDO::FETCH_ASSOC);

$getLists = $db->prepare("SELECT targetedLists.Name, targetedLists.Description, targetedLists.ID FROM listSenders INNER JOIN targetedLists ON targetedLists.ID = listSenders.List WHERE listSenders.User = ?");
$getLists->execute([
  $id
]);
$list = $getLists->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

$pagetitle = htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) . ' Targeted List Access';

include BASE_PATH . "views/header.php";

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("users")?>">Users</a></li>
      <li class="breadcrumb-item"><a href="<?=autoUrl("users/" . $id)?>"><?=htmlspecialchars($info['Forename'] . ' ' . $info['Surname'])?></a></li>
      <li class="breadcrumb-item active" aria-current="page">Targeted Lists</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>
        <?=htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname']))?><br><small>Targeted list access</small>
      </h1>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AssignListSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['AssignListSuccess']) { ?>
      <div class="alert alert-success">
        <p class="mb-0">
          <strong>
            We've assigned that list to <?=htmlspecialchars($info['Forename'])?>
          </strong>
        </p>
      </div>
      <?php
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['AssignListSuccess']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RemoveListSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['RemoveListSuccess']) { ?>
      <div class="alert alert-success">
        <p class="mb-0">
          <strong>
            We've removed access to that list from <?=htmlspecialchars($info['Forename'])?>
          </strong>
        </p>
      </div>
      <?php
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['RemoveListSuccess']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RemoveListError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['RemoveListError']) { ?>
      <div class="alert alert-danger">
        <p class="mb-0">
          <strong>
            We could not remove access to that list from <?=htmlspecialchars($info['Forename'])?>
          </strong>
        </p>
      </div>
      <?php
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['RemoveListError']);
      } ?>

      <?php if ($list != null) { ?>
      <p>
        This user is authorised to send notify emails to members of the following targeted lists
      </p>
      <ul class="list-group mb-3">
        <?php do { ?>
        <li class="list-group-item">
          <div class="row align-items-center justify-content-between">
            <div class="col">
              <p class="mb-0"><strong><?=htmlspecialchars($list['Name'])?></strong></p>
              <p class="mb-0"><?=htmlspecialchars($list['Description'])?></p>
            </div>
            <div class="col text-end">
              <span>
                <a class="btn btn-primary" href="<?=autoUrl("users/" . $id . "/targeted-lists/remove?list=" . $list['ID'] . "")?>">Remove</a>
              </span>
            </div>
          </div>
        </li>
        <?php } while ($list = $getLists->fetch(PDO::FETCH_ASSOC)); ?>
      </ul>
      <?php } else { ?>
      <div class="alert alert-warning">
        <p class="mb-0">
          <strong>
            <?=htmlspecialchars($info['Forename'])?> has not been assigned permissions to send notify emails to any targeted lists
          </strong>
        </p>
      </div>
      <?php } ?>

      <p>
        <a href="<?=autoUrl("users/" . $id . "/targeted-lists/add")?>" class="btn btn-primary">
          Assign a list
        </a>
      </p>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();