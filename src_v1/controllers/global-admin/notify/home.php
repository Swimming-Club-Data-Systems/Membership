<?php

$pagetitle = "Notify - Admin Dashboard - SCDS";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item active" aria-current="page">Notify</li>
    </ol>
  </nav>

  <h1>
    Notify System
  </h1>
  <p class="lead">Notify is our <strong>GDPR Compliant</strong> email system for contacting parents and users.</p>

  <?php if (isset($_SESSION['SCDS-Notify']['Success'])) { ?>
    <div class="alert alert-success">
      <p class="mb-0">
        <strong>Message sent successfully</strong>
      </p>
    </div>
  <?php unset($_SESSION['SCDS-Notify']['Success']);
  } ?>

  <div class="list-group">
    <a href="<?= htmlspecialchars(autoUrl('admin/notify/history')) ?>" class="list-group-item list-group-item-action">Message History</a>
    <a href="<?= htmlspecialchars(autoUrl('admin/notify/compose')) ?>" class="list-group-item list-group-item-action">New Email</a>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();
