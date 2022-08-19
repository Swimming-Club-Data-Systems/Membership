<?php

$pagetitle = "Direct Debit Set Up Successfully";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin')) ?>">Admin</a></li>
      <li class="breadcrumb-item active" aria-current="page">Direct Debit</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>Direct Debit Set Up Successfully</h1>
      <p class="lead">You have set up a Direct Debit Instruction successfully.</p>

      <p><a href="<?= htmlspecialchars(autoUrl('admin/scds-payments')) ?>">Visit the SCDS Payment Portal</a>.</p>

      <p><a href="<?= htmlspecialchars(autoUrl('admin')) ?>">Return to admin homepage</a>.</p>

    </div>

    <div class="col">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/admin-tools/list.json'));
      echo $list->render('scds-payments');
      ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
