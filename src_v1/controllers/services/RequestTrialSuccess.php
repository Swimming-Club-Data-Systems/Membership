<?php

$pagetitle = "Request a Trial Form";
$use_white_background = true;
$use_website_menu = true;

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <h1>Request a Trial</h1>
  <div class="row">
    <div class="col-md-10 col-lg-8">
      <p class="lead">
        Your trial request has been sent successfully. An email confirming this
        should be on the way to you now.
      </p>

      <p>
        We'll be in touch as soon as we can with details about a trial. At busy
        times, this may take a few days.
      </p>

      <p>
        <a href="<?=htmlspecialchars(autoUrl("services/request-a-trial"))?>" class="btn btn-success">Request another trial</a>
      </p>
    </div>
  </div>
</div>

<?php

unset($_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Success']);

$footer = new \SCDS\Footer();
$footer->render();
