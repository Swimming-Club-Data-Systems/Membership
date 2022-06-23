<?php

$date = new DateTime('now', new DateTimeZone('Europe/London'));

$pagetitle = "Admin Reports";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin")) ?>">Admin</a></li>
      <li class="breadcrumb-item active" aria-current="page">Reports</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>Admin reports</h1>
      <p class="lead">Welcome to the admin reports dashboard.</p>

      <h2>Membership reports</h2>
      <ul>
        <li><a download href="<?= htmlspecialchars(autoUrl("admin/reports/membership-data-export.csv")) ?>">Membership report (CSV download)</a></li>
        <li><a download href="<?= htmlspecialchars(autoUrl("admin/reports/photography-permissions-export.csv")) ?>">Photography permissions (CSV download)</a></li>
        <li><a href="<?= htmlspecialchars(autoUrl("members/reports/upgradeable")) ?>">Upgradeable to Category 2</a></li>
        <li><a href="<?= htmlspecialchars(autoUrl("admin/reports/no-email-subscription")) ?>">Users not opted in to receive notify emails</a></li>
        <li><a href="<?= htmlspecialchars(autoUrl("admin/reports/adult-members")) ?>">Adult Members</a></li>
        <li><a href="<?= htmlspecialchars(autoUrl("admin/reports/junior-league-report")) ?>">List of members valid for Junior or Arena League</a></li>
      </ul>

      <h2>Finance reports</h2>
      <ul>
        <li><a download href="<?= htmlspecialchars(autoUrl("admin/reports/pending-payments-data-export.csv")) ?>">Pending payments report (CSV download)</a></li>
        <li><a href="<?= htmlspecialchars(autoUrl("payments/history/" . $date->format("Y/m"))) ?>">Payment payout report for <?= htmlspecialchars($date->format("M Y")) ?> (various formats)</a></li>
      </ul>

    </div>

    <div class="col">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/admin-tools/list.json'));
      echo $list->render('admin-reports');
      ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
