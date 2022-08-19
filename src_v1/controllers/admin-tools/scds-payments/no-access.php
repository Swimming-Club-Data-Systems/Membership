<?php

$pagetitle = "Unauthorised - SCDS Payments";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item active" aria-current="page">Admin</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>You don't have access to SCDS Payment Management</h1>
      <p class="lead">SCDS Payment Management allows select club staff to manage automated payments to Swimming Club Data Systems.</p>

      <p>Admins can enable access to SCDS Payment Management if you require access.</p>

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