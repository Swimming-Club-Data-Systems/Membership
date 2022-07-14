<?php

$user = app()->user;
$db = app()->db;

$today = new DateTime('now', new DateTimeZone('Europe/London'));

$getPending = $db->prepare("SELECT membershipBatch.ID id, DueDate due, Total total, PaymentTypes payMethods FROM membershipBatch WHERE User = ? AND (DueDate >= ? OR DueDate IS NULL) AND NOT Completed AND NOT Cancelled");
$getPending->execute([
	$user->getId(),
	$today->format('Y-m-d'),
]);

$pagetitle = "Membership Centre";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
	<div class="container-xl">

		<!-- Page header -->
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item active" aria-current="page">Memberships</li>
			</ol>
		</nav>

		<div class="row align-items-center">
			<div class="col-lg-8">
				<h1>
					Membership Centre
				</h1>
				<p class="lead mb-0">
					Welcome to the membership centre
				</p>
			</div>
		</div>
	</div>
</div>

<div class="container-xl">

	<div class="row">
		<div class="col-lg-8">
			<p>
				The Membership Centre lets clubs track which memberships their members hold in a given year.
			</p>

			<?php if ($pending = $getPending->fetch(PDO::FETCH_OBJ)) { ?>
				<h2>Your memberships pending payment</h2>

				<p class="lead">
					Here are your pending membership fee payments. Please view the batch to review and pay.
				</p>

				<ul class="list-group mb-3">
					<?php do {

						$payMethods = json_decode($pending->payMethods);

					?>
						<li class="list-group-item">

							<h3>Membership Batch</h3>

							<dl class="row">

								<dt class="col-3">
									Batch ID
								</dt>
								<dd class="col-9">
									<?= htmlspecialchars($pending->id) ?>
								</dd>

								<dt class="col-3">
									Amount
								</dt>
								<dd class="col-9">
									<?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($pending->total), 'GBP')) ?>
								</dd>

								<dt class="col-3">
									Pay by
								</dt>
								<dd class="col-9">
									<?php if (sizeof($payMethods) > 0) { ?>
										<ul class="mb-0">
											<?php if (in_array('card', $payMethods)) { ?>
												<li>Credit/debit card</li>
											<?php } ?>
											<?php if (in_array('dd', $payMethods)) { ?>
												<li>Next Direct Debit payment</li>
											<?php } ?>
										<?php } else { ?>
											No payment methods - speak to club staff
										<?php } ?>
								</dd>
							</dl>

							<p class="mb-0">
								<a href="<?= htmlspecialchars(autoUrl("memberships/batches/" . $pending->id)) ?>" class="btn btn-primary">View and pay</a>
							</p>
						</li>
					<?php } while ($pending = $getPending->fetch(PDO::FETCH_OBJ)); ?>
				</ul>
			<?php } else { ?>

				<div class="alert alert-info">
					<p class="mb-0">
						<strong>You currently have no additional membership fees to pay</strong>
					</p>
					<p class="mb-0">
						Speak to a member of club staff if you expected to see a payment here.
					</p>
				</div>

			<?php } ?>

		</div>
	</div>

	<?php if (app()->user->hasPermission('Admin')) { ?>

		<h2>Membership administration</h2>
		<p class="lead">
			Registration, renewal and additional fees are now all in one place.
		</p>

		<div class="row mb-0">

			<div class="col-md-6 col-lg-4 pb-3">
				<div class="card card-body h-100" style="display: grid;">
					<div>
						<h3>
							Onboard a new user or member
						</h3>
						<p class="lead">
							This replaces Assisted Registration.
						</p>
					</div>
					<p class="mb-0 mt-auto d-flex">
						<a href="<?= htmlspecialchars(autoUrl('onboarding')) ?>" class="btn btn-primary">
							Go
						</a>
					</p>
				</div>
			</div>

			<div class="col-md-6 col-lg-4 pb-3">
				<div class="card card-body h-100" style="display: grid;">
					<div>
						<h3>
							Membership Renewal
						</h3>
						<p class="lead">
							Manage renewal period.
						</p>
					</div>
					<p class="mb-0 mt-auto d-flex">
						<a href="<?= htmlspecialchars(autoUrl('memberships/renewal')) ?>" class="btn btn-primary">
							Go
						</a>
					</p>
				</div>
			</div>

			<div class="col-md-6 col-lg-4 pb-3">
				<div class="card card-body h-100" style="display: grid;">
					<div>
						<h3>
							Onboarding Sessions
						</h3>
						<p class="lead">
							View, edit or create onboarding sessions.
						</p>
					</div>
					<p class="mb-0 mt-auto d-flex">
						<a href="<?= htmlspecialchars(autoUrl('onboarding/all')) ?>" class="btn btn-primary">
							Go
						</a>
					</p>
				</div>
			</div>

			<div class="col-md-6 col-lg-4 pb-3">
				<div class="card card-body h-100" style="display: grid;">
					<div>
						<h3>
							Membership Batches
						</h3>
						<p class="lead">
							Bill a member for new membership types.
						</p>
					</div>
					<p class="mb-0 mt-auto d-flex">
						<a href="<?= htmlspecialchars(autoUrl('memberships/batches')) ?>" class="btn btn-primary">
							Go
						</a>
					</p>
				</div>
			</div>

			<div class="col-md-6 col-lg-4 pb-3">
				<div class="card card-body h-100" style="display: grid;">
					<div>
						<h3>
							Membership Years and Periods
						</h3>
						<p class="lead">
							Add or edit periods.
						</p>
					</div>
					<p class="mb-0 mt-auto d-flex">
						<a href="<?= htmlspecialchars(autoUrl('memberships/years')) ?>" class="btn btn-primary">
							Go
						</a>
					</p>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-lg-8">
				<h2>Legacy Membership Renewal</h2>

				<p>
					The legacy membership renewal system is deprecated. You cannot create, edit or complete a legacy renewal or assisted registration session anymore as the functionality has been removed. You can however view remaining data from these renewals.
				</p>

				<p>
					<a href="<?= htmlspecialchars(autoUrl('renewal')) ?>" class="btn btn-primary">
						View legacy renewal periods
					</a>
				</p>
			</div>
		</div>
	<?php } ?>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
