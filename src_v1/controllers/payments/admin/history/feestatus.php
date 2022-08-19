<?php

// require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$db = app()->db;
$tenant = app()->tenant;

use Respect\Validation\Validator as v;

if (!v::intVal()->between(1, 12)->validate((int) $month) || !v::stringType()->length(2, 2)->validate($month)) {
	halt(404);
}

if (!v::intVal()->min(2000, true)->validate((int) $year) || !v::stringType()->length(4, null)->validate($year)) {
	halt(404);
}

$searchDate = $year . "-" . $month . "-" . "%";
$name_type = null;
$title_string = null;

$fluidContainer = true;
$use_white_background = true;

$dateString = date("F Y", strtotime($year . "-" . $month));

$table_sm;
if (app('request')->isMobile()) {
	$table_sm = "table-sm";
}

if ($type == "squads") {
	$name_type = "SquadFee";
	$title_string = "Squad Fee payments for " . $dateString;
} else if ($type == "extras") {
	$name_type = "ExtraFee";
	$title_string = "Extra Fee payments for " . $dateString;
} else {
	halt(404);
}

$pagetitle = "Status - " . $dateString;

$getPayments = $db->prepare("SELECT `Forename`, `Surname`, `MForename`, `MSurname`,
individualFeeTrack.Amount, individualFeeTrack.Description, payments.Status, payments.PaymentID, users.UserID, metadataJSON, individualFeeTrack.MemberID, payments.stripeFailureCode FROM
(((((`individualFeeTrack` LEFT JOIN `paymentMonths` ON
individualFeeTrack.MonthID = paymentMonths.MonthID) LEFT JOIN `paymentsPending`
ON individualFeeTrack.PaymentID = paymentsPending.PaymentID) LEFT JOIN
`members` ON members.MemberID = individualFeeTrack.MemberID) LEFT JOIN
`payments` ON paymentsPending.Payment = payments.PaymentID) LEFT JOIN `users` ON
users.UserID = individualFeeTrack.UserID) WHERE `paymentMonths`.`Date` LIKE
? AND `individualFeeTrack`.`Type` = ? AND users.Tenant = ? ORDER BY `Forename`
ASC, `Surname` ASC, `users`.`UserID` ASC, `MForename` ASC, `MSurname` ASC;");
$getPayments->execute([
	$searchDate,
	$name_type,
	$tenant->getId()
]);

/*
SELECT `Forename`, `Surname`, `MForename`, `MSurname`,
individualFeeTrack.Amount, individualFeeTrack.Description, payments.Status FROM
(((((`individualFeeTrack` LEFT JOIN `paymentMonths` ON
individualFeeTrack.MonthID = paymentMonths.MonthID) LEFT JOIN `paymentsPending`
ON individualFeeTrack.PaymentID = paymentsPending.PaymentID) LEFT JOIN
`members` ON members.MemberID = individualFeeTrack.MemberID) LEFT JOIN
`payments` ON paymentsPending.PMkey = payments.PMkey) LEFT JOIN `users` ON
users.UserID = individualFeeTrack.UserID) WHERE `paymentMonths`.`Date` LIKE
'$searchDate' AND `individualFeeTrack`.`Type` = '$name_type' ORDER BY `Forename`
ASC, `Surname` ASC, `users`.`UserID` ASC, `MForename` ASC, `MSurname` ASC
 */

//pre($sql);
$row = $getPayments->fetch(PDO::FETCH_ASSOC);

$url = autoUrl("payments/history/" . $type . "/" . $year . "/" . $month);

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<div class="container-fluid">
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= autoUrl("payments") ?>">Payments</a></li>
			<li class="breadcrumb-item"><a href="<?= autoUrl("payments/history") ?>">History &amp; Status</a></li>
			<li class="breadcrumb-item active" aria-current="page"><?= $dateString ?></li>
		</ol>
	</nav>

	<h1>Status for <?= $dateString ?></h1>
	<p class="lead"><?= $title_string ?></p>
	<p><a href="<?= htmlspecialchars($url . "/csv") ?>" target="_blank">View as CSV (Comma Separated Values)</a> or <a href="<?= htmlspecialchars($url . "/json") ?>" target="_blank">View as JSON (JavaScript Object Notation)</a></p>
	<?php if ($row == null) { ?>
		<div class="alert alert-warning mb-0">
			<p class="mb-0">
				<strong>
					No fees can be found for this period
				</strong>
			</p>
			<p class="mb-0">
				We are sorry for the inconvenience caused.
			</p>
		</div>
	<?php } else { ?>
		<div class="table-responsive-md">
			<table class="table mb-0 <?= $table_sm ?> table-light">
				<thead">
					<tr>
						<th>
							Parent
						</th>
						<th>
							Swimmer
						</th>
						<th>
							Amount
						</th>
						<th>
							Status
						</th>
					</tr>
					</thead>
					<tbody>
						<?php
						$link;
						do {
							$metadata = json_decode($row['metadataJSON']);
							$swimmer_name = null;
							if (isset($metadata->Members)) {
								for ($j = 0; $j < sizeof($metadata->Members); $j++) {
									if ($metadata->Members[$j]->Member == $row['MemberID']) {
										$swimmer_name = htmlspecialchars($metadata->Members[$j]->MemberName);
									}
								}
							}
						?>
							<?php if ($row['Status'] == "confirmed" || $row['Status'] == "paid_out" || $row['Status'] == "paid_manually" || $row['Status'] == "succeeded") {
							?><tr class="table-success"><?php
																					$link = "text-success";
																				} else if ($row['Status'] == "failed" || $row['Status'] == "charged_back" || $row['Status'] == "canceled" || $row['Status'] == "requires_payment_method" || $row['Status'] == null) {
																					?>
								<tr class="table-danger"><?php
																					$link = "text-danger";
																				} else if ($row['Status'] == "cust_not_dd") {
																					?>
								<tr class="table-warning"><?php
																					$link = "text-warning";
																				} else { ?>
								<tr class=""><?php $link = "";
																				} ?>
								<td>
									<?php if ($row['Forename'] != null && $row['Surname'] != null) { ?>
										<?= htmlspecialchars(\SCDS\Formatting\Names::format($row['Forename'], $row['Surname'])) ?><br>
										<small><strong>
												<a target="_blank" href="<?= autoUrl("notify/newemail/individual/" . $row['UserID']) ?>">
													Contact Parent
												</a>
											</strong></small>
									<?php } else { ?>
										No Parent
									<?php } ?>
								<td>
									<ul class="list-unstyled mb-0">
										<?php if ($row['MForename'] == null || $row['MSurname'] == null || $row['MForename'] == "" || $row['MSurname'] == "") { ?>
											<li><?= $swimmer_name ?></li>
										<?php } else { ?>
											<li><?= htmlspecialchars(\SCDS\Formatting\Names::format($row['MForename'], $row['MSurname'])) ?></li>
										<?php } ?>
										<li><em><?= htmlspecialchars($row['Description']) ?></em></li>
									</ul>
								</td>
								<td>
									&pound;<?= htmlspecialchars(number_format(($row['Amount'] / 100), 2, '.', '')) ?>
								</td>
								<td>
									<?php if ($row['Forename'] != null && $row['Surname'] != null) {
										echo paymentStatusString($row['Status'], $row['stripeFailureCode']);
									} else {
										echo "No Parent or Direct Debit Available";
									} ?>
								</td>
								</tr>
							<?php	} while ($row = $getPayments->fetch(PDO::FETCH_ASSOC)); ?>
					</tbody>
			</table>
		</div>
	<?php } ?>
</div>

<?php $footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();
