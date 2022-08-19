<?php

$pagetitle = "Audit Logs";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
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

      <p>
        <a href="<?= htmlspecialchars(autoUrl('admin/audit/requests')) ?>" class="btn btn-primary">View HTTP request logs</a>
      </p>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();
