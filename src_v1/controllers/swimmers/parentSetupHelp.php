<?php

$db = app()->db;
$tenant = app()->tenant;

$sql = $db->prepare("SELECT * FROM `members` WHERE members.Tenant = ? AND `MemberID` = ?");
$sql->execute([
  $tenant->getId(),
  $id
]);

$row = $sql->fetch(PDO::FETCH_ASSOC);

if ($row == null) {
	halt(404);
}
$swimEnglandText = "Swim England Number";
if (mb_stripos($row['ASANumber'], app()->tenant->getKey('ASA_CLUB_CODE')) > -1) {
	$swimEnglandText = "Temporary Membership Number";
}

$_SESSION['TENANT-' . app()->tenant->getId()]['qr'][0]['text'] = autoUrl("my-account/addswimmer/auto/" . $row['ASANumber'] . "/" . $row['AccessKey']);

$pagetitle = htmlspecialchars($row['MForename'] . " " . $row['MSurname']) . " Registration Information";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/swimmersMenu.php";

?>
<style>
@media print {
  .bg-primary {
    color-adjust: exact;
    -webkit-print-color-adjust: exact;
    background: #bd0000 !important;
    color: #fffffe !important;
  }

  .text-white {
    color-adjust: exact;
    -webkit-print-color-adjust: exact;
    color: #fffffe !important;
  }

  html,
  body {
    background: #ffffff !important;
    font-size: 12pt;
    padding: 0px !important;
    margin-top: 0px !important;
  }
}
</style>
<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("members")?>">Members</a></li>
      <li class="breadcrumb-item"><a href="<?=autoUrl("members/" . $id)?>"><?=htmlspecialchars($row['MForename'])?>
          <?=htmlspecialchars(mb_substr($row['MSurname'], 0, 1, 'utf-8'))?></a></li>
      <li class="breadcrumb-item active" aria-current="page">Access key</li>
    </ol>
  </nav>

  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['EmailStatus']) && $_SESSION['TENANT-' . app()->tenant->getId()]['EmailStatus']) { ?>
  <div class="alert alert-success d-print-none">
    <p class="mb-0">We've sent an email to that address.</p>
  </div>
  <?php } else if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['EmailStatus']) && !$_SESSION['TENANT-' . app()->tenant->getId()]['EmailStatus']) { ?>
  <div class="alert alert-danger d-print-none">
    <p class="mb-0">We were unable to send an email to that address.</p>
  </div>
  <?php }
  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['EmailStatus'])) {
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['EmailStatus']);
  } ?>

	<div class="alert alert-danger d-print-none">
		<p class="mb-0">
      <strong>Account setup using access keys has been deprecated.</strong> Please use <a href="<?=htmlspecialchars(autoUrl("assisted-registration"))?>" class="alert-link">Assisted Registration</a> instead.
    </p>
		<p class="mb-0">
			Support for access keys will be removed in a future update.
		</p>
	</div>

  <div class="alert alert-info d-print-none">
    <p class="mb-0">
      <strong>
        Instructions for if you are not using assisted registration.
      </strong>
    </p>
    <p>
      Please print out, email or copy the information below to give to the
      parent of this new swimmer. It contains the details they need to connect
      this swimmer to their account online.
    </p>
    <p>
      This message will not be shown on the print out.
    </p>
		<p class="mb-0">
			<a target="_self" class="btn btn-info" href="javascript:window.print()">
				<i class="fa fa-print" aria-hidden="true"></i> Print
			</a>
		</p>
    <!-- <form method="post">
      <div class="mb-3">
        <label class="form-label" for="emailAddr">Send to email address</label>
        <input type="email" class="form-control" id="emailAddr" name="emailAddr" aria-describedby="emailAddrHelp"
          placeholder="Enter email">
        <small id="emailAddrHelp" class="form-text text-muted">Sends a one-off email.</small>
      </div>
      <?=SCDS\CSRF::write()?>
    </form> -->
  </div>

  <div class="py-3 mb-3 text-end font-monospace">
    <?=htmlspecialchars(app()->tenant->getCode())?> MEMBER ID: <?= htmlspecialchars($id) ?>
  </div>

  <div class="mb-3 p-5 bg-primary text-white">
    <span class="h3 mb-0"><?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?></span>
    <h1 class="h2 mb-4">Online Membership System</h1>
    <p class="mb-0">
      <strong>
        Your Access Key for <?=htmlspecialchars($row['MForename'] . " " . $row['MSurname'])?>
      </strong>
    </p>
  </div>

  <p>
    Here at <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>, we provide a number of online services to
    manage our members. Our services allow you to manage your swimmers, enter competitions, stay up to date by email and make payments by Direct Debit or credit/debit card.
  </p>

  <?php if (!app()->tenant->isCLS()) { ?>
  <p>
    <em>Some services may not be provided by your club.</em>
  </p>
  <?php } ?>

  <p>
    If you haven't already done so, you will need to create an account on our
    membership system. This is easy to do - You only need to fill out one form
    and then verify your email address.
  </p>

  <p>
    Here’s what you will need to do to add <?=htmlspecialchars($row['MForename'] . " " . $row['MSurname'])?> to your
    account in our Online Membership System.
  </p>
  
	<!-- <h2>Add via QR Code</h2>

	<p>
		If you have a device that can read QR Codes (A built in feature on iOS and
		Android Devices), scan the QR Code below. You'll be taken to a page where
		you'll be asked to log in, if you aren't already, and we'll automatically
		add <?=htmlspecialchars($row['MForename'])?> to your account.
	</p>

  <div class="border border-dark p-2 bg-white mb-3 px-5">
		<div class="row d-flex align-items-center">
			<div class="col-8">
				<span class="h2">Already registered for an account?</span>
				<p class="lead">
					Scan this QR Code to add <?=htmlspecialchars($row['MForename'])?> quickly.
				</p>
				<p class="mb-0">
					This QR Code cannot be used once you've added this swimmer.
					Contact our support team for a replacement.
				</p>
			</div>
			<div class="col-4 text-center">
        <img class="img-fluid ms-auto d-block" src="<?=autoUrl("services/qr/0/200")?>" srcset="<?=autoUrl("services/qr/0/400")?> 2x, <?=autoUrl("services/qr/0/600")?> 3x" alt="<?=htmlspecialchars(autoUrl("my-account/addswimmer/auto/" . $row['ASANumber'] . "/" . $row['AccessKey']))?>" title="<?=htmlspecialchars(autoUrl("my-account/addswimmer/auto/" . $row['ASANumber'] . "/" . $row['AccessKey']))?>"></img>
			</div>
		</div>
  </div>

	<h2>Add Manually</h2> -->

  <p>
    Log into your account at <?=autoUrl("")?> and the select 'My Account' then 'Add Members' from the menu at the top.
  </p>

  <p>
    You'll be directed to a page and asked to enter your swimmer's <?=$swimEnglandText?> and <?=htmlspecialchars(app()->tenant->getKey('CLUB_SHORT_NAME'))?> Access Key as below.
  </p>

  <div class="mb-3">
    <table class="table table-sm table-borderless d-inline mb-0">
      <tbody>
        <tr>
          <th scope="row" class="ps-0"><?=$swimEnglandText?></th>
          <td class="pe-0"><span class="font-monospace"><?=htmlspecialchars($row['ASANumber'])?></span></td>
        </tr>
        <tr>
          <th scope="row" class="ps-0"><?=htmlspecialchars(app()->tenant->getKey('CLUB_SHORT_NAME'))?> Access Key</th>
          <td class="pe-0"><span class="font-monospace"><?=htmlspecialchars($row['AccessKey'])?></span></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="small text-muted">
    <p>
      Access Keys are unique for each swimmer and ensure that the right people
      add a swimmer to their account. To increase data security, we will
      regenerate access keys when you add a swimmer or remove a swimmer from
      your account. If you remove a swimmer, or want to move them to a different
      account, ask a committee member for a new access key. The committee member
      may need to verify your identity.
    </p>

    <?php if (mb_stripos($row['ASANumber'], app()->tenant->getKey('ASA_CLUB_CODE')) > -1) { ?>
    <p>
      At the time you were given this form we did not yet have a Swim England registration number for
      <?=htmlspecialchars($row['MForename'])?>. We have given you a temporary number which starts with <span
        class="font-monospace"><?=htmlspecialchars(app()->tenant->getKey('ASA_CLUB_CODE'))?></span> which you can use to add your swimmer to your
      account.
    </p>
    <?php } ?>

    <p>
      If you’d like more information about how we use data, contact
      <?=htmlspecialchars(app()->tenant->getKey('CLUB_EMAIL'))?>.
    </p>

    <?php if (!app()->tenant->isCLS()) { ?>
    <p>
      The membership system is provided to <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> by
      <a href="https://wwww.myswimmingclub.uk/">Swimming Club Data Systems</a>.
    </p>
    <?php } ?>
  </div>
</div>
<?php $footer = new \SCDS\Footer();
$footer->render();