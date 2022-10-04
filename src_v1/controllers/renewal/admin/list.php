<?php

$db = app()->db;
$tenant = app()->tenant;

$sql = $db->prepare("SELECT * FROM `renewals` WHERE `ID` = ? AND Tenant = ?;");
$sql->execute([
	$id,
	$tenant->getId()
]);
$renewalArray = $sql->fetch(PDO::FETCH_ASSOC);

if ($renewalArray == null) {
	halt(404);
}

$getNumRenewals = $db->prepare("SELECT COUNT(*) FROM `renewalMembers` WHERE `RenewalID` = ? AND Renewed = ?;");
$getNumRenewals->execute([$id, true]);
$numRenewals = $getNumRenewals->fetchColumn();

$getNumRenewals->execute([$id, 0]);
$numMembers = $numRenewals + $getNumRenewals->fetchColumn();

$getCategories = $db->prepare("SELECT `Name`, `ID` FROM `clubMembershipClasses` WHERE `Type` = ? AND `Tenant` = ? ORDER BY `Name` ASC");
$getCategories->execute([
	'national_governing_body',
	$tenant->getId()
]);
$cats = [];

$numRenewalsByCat = $db->prepare("SELECT COUNT(*) FROM (`renewalMembers` LEFT JOIN `members` ON `members`.`MemberID` = `renewalMembers`.`MemberID`) WHERE `RenewalID` = ? AND members.`NGBCategory` = ? AND Renewed = ?;");

while ($category = $getCategories->fetch(PDO::FETCH_ASSOC)) {
	$numRenewalsByCat->execute([
		$id,
		$category['ID'],
		true,
	]);

	$count = $numRenewalsByCat->fetchColumn();

	$cats[] = [
		'id' => $category['ID'],
		'name' => $category['Name'],
		'count' => (int) $count
	];
}

$sql = $db->prepare("SELECT `MForename`, `MSurname`, `Forename`, `Surname`, members.ASANumber, `payments`.`Status`, `RenewalID`, `Renewed`, stripePayments.ID StripeDBID, stripePayments.Paid StripePaid, stripePayMethods.Last4, stripePayMethods.Brand, stripePayMethods.Funding, `clubMembershipClasses`.`Name` ASACategory, members.Active, renewalMembers.MemberID, renewalMembers.ID RMID, renewalMembers.Date FROM (((((((`renewalMembers` RIGHT JOIN `members`
ON members.MemberID = renewalMembers.MemberID) LEFT JOIN `users` ON
members.UserID = users.UserID) LEFT JOIN `paymentsPending` ON
renewalMembers.PaymentID = paymentsPending.PaymentID) LEFT JOIN `payments` ON
payments.PMkey = paymentsPending.PMkey) LEFT JOIN `stripePayments` ON
stripePayments.ID = renewalMembers.StripePayment) LEFT JOIN `stripePayMethods` ON
stripePayments.Method = stripePayMethods.ID) LEFT JOIN clubMembershipClasses ON members.NGBCategory = clubMembershipClasses.ID) WHERE `renewalMembers`.`RenewalID` =
? ORDER BY members.Active DESC, renewalMembers.Date DESC, `Surname` ASC, `Forename` ASC, members.UserID ASC, `MSurname` ASC, `MForename` ASC;");
$sql->execute([$id]);

$fluidContainer = true;

$from = new DateTime($renewalArray['StartDate'], new DateTimeZone('Europe/London'));
$to = new DateTime($renewalArray['EndDate'], new DateTimeZone('Europe/London'));
$earliest = new DateTime('2000-01-01', new DateTimeZone('Europe/London'));

$latestDate = null;

$pagetitle = "Membership Renewal";
include BASE_PATH . "views/header.php";
include BASE_PATH . "views/swimmersMenu.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
	<div class="container-fluid">

		<!-- Page header -->
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("renewal")) ?>">Renewal</a></li>
				<li class="breadcrumb-item active" aria-current="page">#<?= htmlspecialchars($id) ?></li>
			</ol>
		</nav>

		<div class="row align-items-center">
			<div class="col-lg-8">
				<h1>
					<?= htmlspecialchars($renewalArray['Name']) ?> Status
				</h1>
				<p class="lead mb-0" id="leadDesc">
					<?= htmlspecialchars($from->format("l j F Y")) ?> to <?= htmlspecialchars($to->format("l j F Y")) ?>
				</p>
				<div class="mb-3 d-lg-none"></div>
			</div>
		</div>
	</div>
</div>

<div class="container-fluid">
	<div class="">
		<p class="mb-0">
			<?php foreach ($cats as $cat) { ?><?= htmlspecialchars($cat['count']) ?> <?= htmlspecialchars($cat['name']) ?>, <?php } ?> of <?= $numMembers ?> members*.
		</p>
		<p class="small text-muted">
			* Number of members on first day of renewal
		</p>
	</div>

	<?php
	$renewalItem = $sql->fetch(PDO::FETCH_ASSOC);
	if ($renewalItem == null) {
		// No renewals
	?>
		<div class="alert alert-warning">
			<p class="mb-0">
				<strong>
					There are no renewals to display at this time.
				</strong>
			</p>
			<p class="mb-0">
				Please try again later.
			</p>
		</div>
	<?php
	} else {  ?>
		<div class="table-responsive-sm">
			<table class="table table-light <?php if (app('request')->isMobile()) { ?>table-sm small<?php } ?>">
				<thead>
					<tr>
						<th>
							Member
						</th>
						<th>
							Parent/User
						</th>
						<th>
							ASA
						</th>
						<th>
							Payment Status
						</th>
					</tr>
				</thead>
				<tbody>
					<?php do {

						$dateCompleted = new DateTime($renewalItem['Date'], new DateTimeZone('UTC'));
						$dateCompleted->setTimezone(new DateTimeZone('Europe/London'));
						if ($latestDate != $dateCompleted->format('Y-m-d') && $dateCompleted > $earliest) { ?>
							<tr class="table-light">
								<td colspan="4">
									<strong><?= htmlspecialchars($dateCompleted->format('l j')) ?><sup><?= htmlspecialchars($dateCompleted->format('S')) ?></sup> <?= htmlspecialchars($dateCompleted->format('F Y')) ?></strong>
								</td>
							</tr>
						<?php
							$latestDate = $dateCompleted->format('Y-m-d');
						} else if ($latestDate != $dateCompleted->format('Y-m-d') && $dateCompleted < $earliest) { ?>
							<tr class="table-light">
								<td colspan="4">
									<strong>Uncompleted</strong>
								</td>
							</tr>
						<?php
							$latestDate = $dateCompleted->format('Y-m-d');
						}

						if ($renewalItem['Status'] == "failed" || $renewalItem['Status'] == "charged_back" || $renewalItem['Status'] == "requires_payment_method") {
						?><tr data-member="<?= htmlspecialchars($renewalItem['MemberID']) ?>" data-renewal-record="<?= htmlspecialchars($renewalItem['RMID']) ?>" class="table-danger"><?php
																																																																																					} else if (bool($renewalItem['StripePaid']) || $renewalItem['Status'] == "paid_out" || $renewalItem['Status'] == "confirmed" || $renewalItem['Status'] == "paid_manually" || $renewalItem['Status'] == "succeeded") {
																																																																																						?>
							<tr data-member="<?= htmlspecialchars($renewalItem['MemberID']) ?>" data-renewal-record="<?= htmlspecialchars($renewalItem['RMID']) ?>" class="table-success"><?php
																																																																																					} else if (!bool($renewalItem['Active'])) {
																																																																																						?>
							<tr data-member="<?= htmlspecialchars($renewalItem['MemberID']) ?>" data-renewal-record="<?= htmlspecialchars($renewalItem['RMID']) ?>" class="table-active"><?php
																																																																																					} else {
																																																																																						?>
							<tr data-member="<?= htmlspecialchars($renewalItem['MemberID']) ?>" data-renewal-record="<?= htmlspecialchars($renewalItem['RMID']) ?>"><?php
																																																																																					}
																																																																											?>
							<td>
								<?= htmlspecialchars(\SCDS\Formatting\Names::format($renewalItem['MForename'], $renewalItem['MSurname'])) ?><?php if (!bool($renewalItem['Active'])) { ?> <em>INACTIVE</em><?php } ?>
							</td>
							<td>
								<?= htmlspecialchars(\SCDS\Formatting\Names::format($renewalItem['Forename'], $renewalItem['Surname'])) ?>
							</td>
							<td>
								<span class="font-monospace">
									<?= htmlspecialchars($renewalItem['ASANumber']) ?><?php if ($renewalItem['ASACategory']) { ?> (<?= htmlspecialchars($renewalItem['ASACategory']) ?>)<?php } ?>
								</span>
							</td>
							<td>
								<?php if (!bool($renewalItem['Renewed'])) { ?>
									Not yet renewed
								<?php } else if (bool($renewalItem['StripePaid'])) { ?>
									<img src="<?= autoUrl("img/stripe/" . $renewalItem['Brand'] . ".svg", false) ?>" style="height: 1rem; width: 1.5rem;"> Paid by <span class="visually-hidden"><?= htmlspecialchars(getCardBrand($renewalItem['Brand'])) ?></span> <?= htmlspecialchars($renewalItem['Funding']) ?> card &middot;&middot;&middot;&middot; <?= htmlspecialchars($renewalItem['Last4']) ?> - <a class="font-weight-bold text-success" href="<?= htmlspecialchars(autoUrl("payments/card-transactions/" . $renewalItem['StripeDBID'])) ?>">SPM<?= htmlspecialchars($renewalItem['StripeDBID']) ?></a>
								<?php } else if ($renewalItem['Status'] == "") { ?>
									Payment not yet processed
								<?php } else { ?>
									<?= htmlspecialchars(paymentStatusString($renewalItem['Status'])) ?>
								<?php } ?>
							</td>
							</tr>
						<?php
					} while ($renewalItem = $sql->fetch(PDO::FETCH_ASSOC)); ?>
				</tbody>
			</table>
		</div>
	<?php } ?>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();
