<?php

$pagetitle = "Bulk Editors";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl("admin"))?>">Admin</a></li>
      <li class="breadcrumb-item active" aria-current="page">Bulk editors</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>Bulk editors</h1>
      <p class="lead">Welcome to the bulk editors dashboard.</p>

      <p>Bulk editors allow you to change details across an entire group of members, users etc quickly and easily.</p>

      <h2>Members</h2>
      <ul>
        <li><a href="<?=htmlspecialchars(autoUrl("members/reports/upgradeable"))?>">Edit SE category of members upgradeable to Category 2</a></li>
        <li><a href="<?=htmlspecialchars(autoUrl("admin/editors/squad-membership"))?>">Edit squad membership</a></li>
      </ul>

    </div>

    <div class="col">
      <?php
        $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/admin-tools/list.json'));
        echo $list->render('admin-editors');
      ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();