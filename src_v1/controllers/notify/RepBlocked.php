<?php

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$pagetitle = "Notify is unavailable for your account";

http_response_code(403);

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/notifyMenu.php";

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item active" aria-current="page">Notify</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8 col-md-10">
      <h1>Notify has been disabled for your account</h1>

      <p class="lead">Please ensure that you are logged in with the correct account to access this resource.</p>
      <hr>
      <p>Please try the following:</p>
      <ul>
        <li>Speak to your administrator if you expected to be able to access the Notify service.</li>
        <li>Please ensure that you are logged in with the correct account to access this resource.</li>
        <li>You may not be authorised to access this resource. Click the <a href="javascript:history.back(1)">Back</a> button to try another link.</li>
      </ul>
      <p>HTTP Error 403 - Forbidden.</p>
      <hr>
      <p class="mt-2"><a href="mailto:support@myswimmingclub.uk" title="Support Hotline">Email</a> or <a href="tel:+441912494320">call SCDS on +44 191 249 4320</a> for help and support if the issue persists.</p>

      <p class="small">
        Provided by Swimming Club Data Systems to <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>.
      </p>
    </div>

    <div class="col">
      <div class="card">
        <div class="card-header">
          Rep tools
        </div>
        <div class="list-group list-group-flush">
          <a href="<?= htmlspecialchars(autoUrl('squad-reps')) ?>" class="list-group-item list-group-item-action">Rep dashboard</a>
          <a href="<?= htmlspecialchars(autoUrl('covid')) ?>" class="list-group-item list-group-item-action">COVID-19 tools</a>
          <a href="<?= htmlspecialchars(autoUrl('squad-reps/list')) ?>" class="list-group-item list-group-item-action">List of reps</a>
          <a href="<?= htmlspecialchars(autoUrl('squad-reps/contact-details')) ?>" class="list-group-item list-group-item-action">My contact details</a>

        </div>
      </div>
    </div>
  </div>
</div>

<?php

unset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyIndivSuccess']);
$footer = new \SCDS\Footer();
$footer->render();
