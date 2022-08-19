<?php

$db = app()->db;

$use_white_background = true;

$url_path = "payments";
if (isset($renewal_trap) && $renewal_trap) {
	$url_path = "renewal/payments";
}

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$sql = $db->prepare("SELECT COUNT(*) FROM `paymentSchedule` WHERE `UserID` = ?;");
$sql->execute([$user]);
$scheduleExists = $sql->fetchColumn();
if ($scheduleExists > 0) {
	$scheduleExists = true;
} else {
	$scheduleExists = false;
}

// Get count mandates
$getCount = $db->prepare("SELECT COUNT(*) FROM paymentMandates WHERE UserID = ? AND InUse = 1");
$getCount->execute([
	$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
]);
$mandateCount = $getCount->fetchColumn();

$pagetitle = "Set up a Direct Debit";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";
 ?>

<div class="container-xl">

	<?php if (!isset($renewal_trap) || !$renewal_trap) { ?>
	<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("payments")?>">Payments</a></li>
			<li class="breadcrumb-item"><a href="<?=autoUrl("payments/mandates")?>">Bank</a></li>
      <li class="breadcrumb-item active" aria-current="page">New</li>
    </ol>
  </nav>
	<?php } ?>

	<div class="row">
		<div class="col-lg-8">
			<div class="mb-3">
				<h1>Setup a Direct Debit to pay <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?></h1>
				<p class="lead">
					Direct Debit is the easiest way to pay your club fees.
				</p>

				<?php if ($mandateCount > 0) { ?>
				<div class="alert alert-info">
					<p class="mb-0">
						<strong>Please do not set up a new direct debit if you are switching current accounts with the <a class="alert-link" href="https://www.currentaccountswitch.co.uk">Current Account Switch Service</a>!</strong>
					</p>
					<p class="mb-0">
						We'll automatically update your details to your new bank when the switch goes through.
					</p>
				</div>
				<?php } ?>

				<h2>To begin, you will need</h2>
				<ul>
					<li>The name of the bank account holder</li>
					<li>Your sort code and bank account number</li>
					<li>The address of the bank account holder</li>
				</ul>

				<p>You must be authorised to create a direct debit mandate on the account.</p>

				<h2>You will not need</h2>
				<ul>
					<li>The name or address of your bank - we'll fetch this automatically</li>
					<li>A second approval for most joint accounts - one person is almost always sufficient for approval</li>
				</ul>

				<p>
					Direct Debit makes payments simpler for everyone involved. Payments are taken automatically, so there is no need to adjust standing orders and payments are automatically marked as paid by our systems.
				</p>
				<p>
					We'll usually generate a bill and charge you your fees on or soon after the first working day of each month. It can take several days for the money to leave your bank account.
				</p>
				<p class=""><a href="
					<?php
					if ($scheduleExists) {
						echo autoUrl($url_path . "/setup/2");
					} else {
						echo autoUrl($url_path . "/setup/1");
					} ?>
					" class="btn btn-success">Setup a Direct Debit</a>
				</p>
				<p class="small mb-0">This won't take long.</p>
			</div>
		</div>
		<div class="col">
			<div class="cell">
			<p class="text-center">
				<img style="max-height:50px;" src="<?php echo
				autoUrl("img/directdebit/directdebit.png", false); ?>" srcset="<?php echo
				autoUrl("img/directdebit/directdebit@2x.png", false); ?> 2x, <?php echo
				autoUrl("img/directdebit/directdebit@3x.png", false); ?> 3x" alt="Direct
				Debit Logo">
			</p>
			<p>
				The Direct Debit Guarantee applies to payments made to
				<?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>
			</p>
			<ul>
				<li>
					This Guarantee is offered by all banks and building societies that
					accept instructions to pay Direct Debits
				</li>
				<li>
					If there are any changes to the amount, date or frequency of your
					Direct Debit <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> will notify you three working
					days in advance of your account being debited or as otherwise
					agreed. If you request <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> to collect a payment,
					confirmation of the amount and date will be given to you at the time
					of the request
				</li>
				<li>
					If an error is made in the payment of your Direct Debit, by
					<?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> or your bank or building society, you are
					entitled to a full and immediate refund of the amount paid from your
					bank or building society
					<ul>
						<li>
							If you receive a refund you are not entitled to, you must pay it
							back when <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> asks you to
						</li>
					</ul>
				</li>
				<li>
					You can cancel a Direct Debit at any time by simply contacting your
					bank or building society. Written confirmation may be required.
					Please also notify us.
				</li>
			</ul>
			<p class="mb-0">Payments are handled by GoCardless on behalf of
			<?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>.</p>
		</div>
	</div>
	</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
