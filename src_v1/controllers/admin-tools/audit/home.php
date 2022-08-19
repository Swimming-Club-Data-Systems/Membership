<?php

$pagetitle = "Audit Logs";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin")) ?>">Admin</a></li>
      <li class="breadcrumb-item active" aria-current="page">Audit</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>Audit</h1>
      <p class="lead">Records of operations by users.</p>

      <p>
        SCDS Membership records the operation of some functionality for audit logs. These are used for monitoring system usage, identifying commonly used features and identifying any users who have conducted sabotage.
      </p>

      <p>
        Audit is a mandatory feature and cannot be disabled. SCDS may review your audit logs without consultation at any time.
      </p>

      <p>
        <a href="<?= htmlspecialchars(autoUrl('admin/audit/logs')) ?>" class="btn btn-primary">View logs</a>
      </p>

    </div>

    <div class="col">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/admin-tools/list.json'));
      echo $list->render('admin-audit');
      ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
