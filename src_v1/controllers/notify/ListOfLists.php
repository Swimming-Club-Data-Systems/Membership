<?php

$db = app()->db;
$tenant = app()->tenant;

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$pagetitle = "Targeted Lists";
$use_white_background = true;

$lists = $db->prepare("SELECT * FROM `targetedLists` WHERE Tenant = ? ORDER BY `Name` ASC");
$lists->execute([
  $tenant->getId()
]);
$row = $lists->fetch(PDO::FETCH_ASSOC);

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/notifyMenu.php";

 ?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl("notify"))?>">Notify</a></li>
      <li class="breadcrumb-item active" aria-current="page">Lists</li>
    </ol>
  </nav>

  <div class="">
    <div class="row">
      <div class="col-md-8">
      	<h1>Targeted Lists</h1>
        <p class="lead">
          Targeted lists are custom mailing lists for messaging groups of members outside of normal squads.
        </p>
        <p>
          A useful use case would be for the Junior League
        </p>
      </div>
    </div>
    <?php if ($row != null) { ?>
      <div class="table-responsive-md">
        <table class="table table-light">
          <thead">
            <tr>
              <th>Name</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody>
          <?php do { ?>
            <tr>
              <td><a href="<?php echo autoUrl("notify/lists/" . $row['ID']); ?>"><?=htmlspecialchars($row['Name'])?></a></td>
              <td><?=htmlspecialchars($row['Description'])?></td>
            </tr>
          <?php } while ($row = $lists->fetch(PDO::FETCH_ASSOC)); ?>
        </tbody>
      </table>
    </div>
    <?php } else { ?>
    <div class="alert alert-info">
      <strong>There are no lists available</strong>
    </div>
    <?php } ?>
    <p class="mb-0">
      <a href="<?php echo autoUrl("notify/lists/new"); ?>"
        class="btn btn-success">
        Add New List
      </a>
    </p>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
