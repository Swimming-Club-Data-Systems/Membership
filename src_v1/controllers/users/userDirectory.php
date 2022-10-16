<?php

$tenant = app()->tenant;

$search = "";
if (isset($_GET['search'])) {
  $search = $_GET['search'];
}

$pagetitle = "Users";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Users</li>
      </ol>
    </nav>

    <h1>User Directory</h1>
    <p class="lead mb-0">All active <?= htmlspecialchars($tenant->getName()) ?> users. Useful for changing account settings.</p>
  </div>
</div>

<div class="container-xl">

  <div class="mb-3 row">
    <label class="col-sm-4 col-md-3 col-lg-2" for="search">Search by Name</label>
    <div class="col-sm-8 col-md-9 col-lg-10">
      <input class="form-control" id="search" name="search" value="<?= htmlspecialchars($search) ?>">
    </div>
  </div>

  <div id="output" data-ajax-url="<?= htmlspecialchars(autoUrl('users/ajax/userList')) ?>" data-page="<?= htmlspecialchars(autoUrl("users")) ?>">
    <div class="ajaxPlaceholder">
      <span class="h1 d-block">
        <i class="fa fa-spin fa-circle-o-notch" aria-hidden="true"></i><br>
        Loading Content
      </span>
      If content does not display, please turn on JavaScript
    </div>
  </div>

</div>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/users/list.js");
$footer->render();
