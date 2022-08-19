<?php

$pagetitle = "Membership System Help - SCDS Membership";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">

  <div class="row justify-content-center py-3">
    <div class="col-lg-8 col-md-10">
      <!-- <div class="h4 text-muted mb-3 font-weight-normal">Help and Support</div> -->
      <div class="bg-primary text-white p-4 mb-4 d-inline-block rounded">
        <h1>Membership System Help</h1>
        <p class="mb-0">
          Get help with membership system features
        </p>
      </div>
      <p class="lead">
        The SCDS Online Membership System enables you to manage your swimmers, enter competitions, stay up to date by email and make payments by Direct Debit.
      </p>

      <div class="card card-body mb-3">
        <h2>Contact SCDS Support</h2>
        <p class="lead mb-0">
          Contact <a href="mailto:support@myswimmingclub.uk">support@myswimmingclub.uk</a> to start a new support request.
        </p>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <h2>Help categories</h2>
          <p class="mb-0">
            Help categories available on our new support site
          </p>
        </div>
        <div class="list-group list-group-flush">
          <a href="<?= htmlspecialchars('help-and-support/members') ?>" class="list-group-item list-group-item-action">Members</a>
          <a href="<?= htmlspecialchars('help-and-support/competitions') ?>" class="list-group-item list-group-item-action">Competitions</a>
          <a href="<?= htmlspecialchars('help-and-support/payments') ?>" class="list-group-item list-group-item-action">Payments</a>
          <a href="<?= htmlspecialchars('help-and-support/notify') ?>" class="list-group-item list-group-item-action">Notify (Emails from your club)</a>
          <a href="<?= htmlspecialchars('help-and-support/onboarding') ?>" class="list-group-item list-group-item-action">Member onboarding, membership years and batches</a>
          <a href="<?= htmlspecialchars('help-and-support/log-books') ?>" class="list-group-item list-group-item-action">Log books</a>
          <a href="<?= htmlspecialchars('help-and-support/emergency-contacts') ?>" class="list-group-item list-group-item-action">Emergency contacts</a>
          <a href="<?= htmlspecialchars('help-and-support/covid-19') ?>" class="list-group-item list-group-item-action">Coronavirus (COVID-19)</a>
          <a href="<?= htmlspecialchars('help-and-support/ancilliary') ?>" class="list-group-item list-group-item-action">GDPR and ancilliary documentation</a>
          <a href="<?= htmlspecialchars('help-and-support/tenant-services') ?>" class="list-group-item list-group-item-action">Tenant Services</a>
          <a href="<?= htmlspecialchars('help-and-support/documentation') ?>" class="list-group-item list-group-item-action">Contributing to our documentation</a>
        </div>
      </div>

      <p class="pt-4">
        We're transferred most help and support documentation over from the Chester-le-Street ASC website. Now this is complete, we'll be expanding our help and support resources over the coming weeks and months.
      </p>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();
