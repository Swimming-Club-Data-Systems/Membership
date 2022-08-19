<?php

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['VerifyEmailSent']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['VerifyEmailSent'])) {
  unset($_SESSION['TENANT-' . app()->tenant->getId()]['VerifyEmailSent']);
}

$pagetitle = "Confirmation email sent";
include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">
  <div class="row justify-content-between">
    <div class="col-md-3 d-none d-md-block">
      <?php
        $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/myaccount/ProfileEditorLinks.json'));
        echo $list->render('email');
      ?>
    </div>
    <div class="col-md-9">
      <h2>Verify your email address</h2>
      <p class="lead">
        We've sent an email to that address. The recipient will have to follow a link to confirm their address.
      </p>

      <p>
        <a href="<?=autoUrl("my-account/email")?>" class="btn btn-success">
          Return to email settings
        </a>
      </p>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->render();
