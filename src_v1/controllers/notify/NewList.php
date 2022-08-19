<?php

$pagetitle = "Add a Targeted List";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/notifyMenu.php";

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl("notify"))?>">Notify</a></li>
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl("notify/lists"))?>">Lists</a></li>
      <li class="breadcrumb-item active" aria-current="page">New</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1 class="">
        Create a new targeted list
      </h1>
      <p class="lead">
        Targeted lists are custom mailing lists for messaging groups of members outside of normal squads.
      </p>
      <p>
        A useful use case would be for the Junior League or National Arena League.
      </p>

      <?php
      if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) {
        echo $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'];
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
      }
      ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label" for="name">List Name</label>
          <input type="text" class="form-control" id="name" name="name" aria-describedby="nameHelp" placeholder="Enter name">
          <small id="nameHelp" class="form-text text-muted">You will look for this name to send messages to the group</small>
        </div>

        <div class="mb-3">
          <label class="form-label" for="desc">Description</label>
          <input type="text" class="form-control" id="desc" name="desc" placeholder="Describe this group">
        </div>

        <p class="mb-0">
          <button type="submit" class="btn btn-success">
            Create new list
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
