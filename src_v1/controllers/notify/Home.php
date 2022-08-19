<?php

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$pagetitle = "Notify";

$use_white_background = true;

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
      <h1>Notify</h1>
      <p class="lead">Notify is our <strong>GDPR Compliant</strong> email system for contacting parents and users.</p>
      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyIndivSuccess'])) {
        if ($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyIndivSuccess']) {?>
      <div class="alert alert-success">
        We've successfully sent your email.
      </div>
      <?php } else {?>
      <div class="alert alert-danger">
        An error occured and we were unable to send your email.
      </div>
      <?php }
      } ?>
      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifySuccess'])) { ?>
      <div class="alert alert-success">
        We've successfully queued your email. <?=
    		$_SESSION['TENANT-' . app()->tenant->getId()]['NotifySuccess']['Count'] ?> people will receive your
        message<?php if (!$_SESSION['TENANT-' . app()->tenant->getId()]['NotifySuccess']['Force']) { ?> if they have
        opted in to recieving emails from us<?php } ?>.
      </div>
      <?php }
      unset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifySuccess']); ?>

      <h2>How to Use Notify</h2>
      <p>
        Notify is available to squad coaches, system administrators and and squad reps.
        It allows you to send an email to parents/account holders of selected groups of members.
        It is possible to create a custom group of members (called a Targeted
        List), or to use a squad or a gala. You can send to any combination of squads and
        lists just by ticking boxes.
      </p>
      <p>
        Click on "Notify Composer" to write an email message. All emails sent
        will be personally addressed to each user who reciceves them.
      </p>
      <p>
        Emails are added to a queue to be sent. Emails will be sent to our
        service provider within one minute who will then send them to all
        users almost instantaneously. Users who have
        not opted in to recieving emails will not receive messages.
      </p>

      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
      <p>
        <strong>
          Please be aware:
        </strong>
        If you Force Send an email, you will be contacted by your System
        Administrator and asked to justify why you did so. This is because of
        our obligations under the GDPR rules. Acceptable use of Force Send
        includes;
      </p>
      <ul>
        <li>Alerting users that sessions have been cancelled</li>
        <li>Sending important gala updates</li>
        <li>Contacting squad users in an emergency</li>
      </ul>
      <p>
        Other use cases are allowed but must be justifiable in terms of our
        operational needs.
      </p>
      <?php } ?>

      <p class="small">
        Provided by Swimming Club Data Systems to <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>.
      </p>
    </div>

    <div class="col">
      <?php $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/notify/list.json')); ?>
      <?=$list->render('notify-home')?>
    </div>
  </div>
</div>

<?php

unset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyIndivSuccess']);
$footer = new \SCDS\Footer();
$footer->render();