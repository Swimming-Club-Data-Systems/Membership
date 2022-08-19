<?php

$tenant = app()->tenant;

$pagetitle = "About this software";
include BASE_PATH . "views/header.php";

?>

<div class="container-xl">
  <div class="row">
    <div class="col-md-8">
      <h1>About this system</h1>
      <p class="lead">This software is licenced to <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> by Swimming Club Data Systems.</p>

      <p>You can use this software by subscribing to the <a href="https://myswimmingclub.uk/" target="_blank">Swimming Club Data Systems Software as a Service Package</a>.</p>

      <h2>
        Getting help
      </h2>

      <p class="lead">
        Something unclear? We can help.
      </p>

      <div class="card card-body mb-3">
        <h3 class="card-title">
          Contact <?= htmlspecialchars(app()->tenant->getName()) ?> in the first instance
        </h3>

        <dl class="row mb-0">
          <dt class="col-sm-3">Email</dt>
          <dd class="col-sm-9 text-truncate mb-0"><a href="mailto:<?= htmlspecialchars(app()->tenant->getKey('CLUB_EMAIL')) ?>"><?= htmlspecialchars(app()->tenant->getKey('CLUB_EMAIL')) ?></a></dd>
        </dl>
      </div>

      <div class="card card-body mb-3">
        <h3 class="card-title">
          If further help is required, contact SCDS
        </h3>

        <dl class="row mb-0">
          <dt class="col-sm-3">Email</dt>
          <dd class="col-sm-9 text-truncate"><a href="mailto:support@myswimmingclub.uk" title="Support Hotline">support@myswimmingclub.uk</a></dd>
        </dl>
      </div>

      <h2>
        Support information
      </h2>

      <p class="lead">
        If we've asked for your tenant details so that we can solve a problem, please send us the following:
      </p>

      <div class="card card-body mb-3">
        <dl class="row mb-0">
          <dt class="col-sm-3">Tenant</dt>
          <dd class="col-sm-9 font-monospace text-truncate"><?= htmlspecialchars($tenant->getUuid()) ?></dd>

          <dt class="col-sm-3">Tenant ID</dt>
          <dd class="col-sm-9 font-monospace"><?= htmlspecialchars($tenant->getId()) ?></dd>

          <dt class="col-sm-3">Tenant Name</dt>
          <dd class="col-sm-9 font-monospace"><?= htmlspecialchars($tenant->getName()) ?></dd>

          <?php if ($tenant->getCode()) { ?>
            <dt class="col-sm-3">Tenant Code</dt>
            <dd class="col-sm-9 font-monospace"><?= htmlspecialchars($tenant->getCode()) ?></dd>
          <?php } ?>

          <?php if (isset(app()->user)) { ?>
            <dt class="col-sm-3">User</dt>
            <dd class="col-sm-9 font-monospace mb-0"><?= htmlspecialchars(app()->user->getId()) ?></dd>
          <?php } else { ?>
            <dt class="col-sm-3">User</dt>
            <dd class="col-sm-9 font-monospace mb-0">Not signed in</dd>
          <?php } ?>
        </dl>
      </div>

      <h2>Features</h2>
      <p class="lead">
        Features include;
      </p>

      <h3>Automatic Member Management</h3>
      <p>The application is built on a database of club members. Members are assigned to squads and parents can link swimmers to their account. This allows us to automatically calculate monthly fees and other things.</p>

      <h3>Online Gala Entries</h3>
      <p>Galas are added to the system by admins. Parents can enter their children into swims by selecting their name, gala and swims. This cuts down on duplicated data from existing arrangements. Parents receive emails detailing their entries.</p>

      <h3>Online Attendance Records</h3>
      <p>Attendance records are online, facilitating automatic attendance calculation. Squads are managed online and swimmer moves between squads can be scheduled in the system.</p>

      <h3>Notify</h3>
      <p>Notify is our E-Mail mailing list solution. Administrators can send emails to selected groups of parents for each squad. The system is GDPR compliant and users can opt in or out of receiving emails at any time.</p>

      <h3>Direct Debit Payments</h3>
      <p>This application has been integrated with GoCardless and their APIs to allow <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> to bill members by Direct Debit. The GoCardless client library which is included in this software is copyright of GoCardless.</p>

      <h2>Legal</h2>
      This product includes GeoLite2 data created by MaxMind, available from <a href="https://www.maxmind.com">https://www.maxmind.com</a>.

    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
