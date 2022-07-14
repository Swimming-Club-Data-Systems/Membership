<?php

$db = app()->db;
$tenant = app()->tenant;

$row = null;

try {
  $list = $db->prepare("SELECT * FROM `targetedLists` WHERE `ID` = ? AND `Tenant` = ?");
  $list->execute([$id, $tenant->getId()]);
} catch (Exception $e) {
  halt(500);
}
$row = $list->fetch(PDO::FETCH_ASSOC);

if ($row == null) {
	halt(404);
}

$pagetitle = "Edit " . htmlspecialchars($row['Name']);

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/notifyMenu.php";

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl("notify"))?>">Notify</a></li>
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl("notify/lists"))?>">Lists</a></li>
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl("notify/lists/" . $id))?>"><?=htmlspecialchars($row['Name'])?></a></li>
      <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1 class="">
  			Edit <?=htmlspecialchars($row['Name'])?>
  		</h1>
      <p class="lead">Edit this targeted list.</p>

      <?php
      if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) {
        echo $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'];
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
      }
      ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label" for="name">List Name</label>
          <input type="text" class="form-control" id="name" name="name"
					placeholder="Enter name" value="<?=htmlspecialchars($row['Name'])?>">
        </div>

        <div class="mb-3">
					<label class="form-label" for="desc">Description</label>
          <input type="text" class="form-control" id="desc" name="desc" placeholder="Describe this group" value="<?=htmlspecialchars($row['Description'])?>">
        </div>

        <p class="mb-0">
          <button type="submit" class="btn btn-success">
            Save Changes
          </button>
        </p>
      </form>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();

?>
