<?php

$db = app()->db;
$tenant = app()->tenant;

$renewals = $db->prepare("SELECT ID, `Name`, StartDate, EndDate FROM `renewals` WHERE Tenant = ? ORDER BY `EndDate` DESC LIMIT 5;");
$renewals->execute([
	$tenant->getId()
]);

$date = new DateTime('now', new DateTimeZone('Europe/London'));
$getRenewals = $db->prepare("SELECT * FROM `renewals` WHERE Tenant = :tenant AND `StartDate` <= :today AND `EndDate` >= :today;");
$getRenewals->execute([
	'tenant' => $tenant->getId(),
	'today' => $date->format("Y-m-d")
]);
$row = $getRenewals->fetch(PDO::FETCH_ASSOC);

$use_white_background = true;
$pagetitle = "Membership Renewal";
include BASE_PATH . "views/header.php";
include BASE_PATH . "views/swimmersMenu.php";
?>

<div class="bg-light mt-n3 py-3 mb-3">
	<div class="container-xl">

		<!-- Page header -->
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item active" aria-current="page">Renewal</li>
			</ol>
		</nav>

		<div class="row align-items-center">
			<div class="col-lg-8">
				<h1>
					Membership Renewal System <span class="badge bg-danger">Deprecated</span>
				</h1>
				<p class="lead mb-0">
					Welcome to the membership renewal system
				</p>
			</div>
		</div>
	</div>
</div>

<div class="container-xl">

	<div class="alert alert-info">
		<p class="mb-0">
			<strong>This is the legacy renewal system which has been deprecated</strong>
		</p>
		<p class="">
			You can no longer create new renewal periods in this system.
		</p>

		<p class="mb-0">
			<a href="<?= htmlspecialchars(autoUrl('memberships/renewal')) ?>" class="alert-link">Visit the new renewal system</a>
		</p>
	</div>

	<div class="">
		<?php if ($row != null) { ?>
			<p>
				Membership renewal ensures all our information about members is up to date.
			</p>
			<p>
				The current membership renewal period is open until <?php echo date("l j F Y", strtotime($row['EndDate'])); ?>
			</p>
		<?php } else { ?>
			<h1>Membership Renewal System</h1>
			<p class="lead">Welcome to the Membership Renewal System</p>
			<p>
				Membership renewal ensures all our information about members is up to
				date.
			</p>
		<?php } ?>
		<h2>Recent renewals</h2>
		<ol>
			<?php while ($row = $renewals->fetch(PDO::FETCH_ASSOC)) {
			?>
				<li>
					<a href="<?= autoUrl("renewal/" . $row['ID']) ?>">
						<?= htmlspecialchars($row['Name']) ?> (<?= date(
																											"j F Y",
																											strtotime($row['StartDate'])
																										) ?> - <?= date(
																														"j F Y",
																														strtotime($row['EndDate'])
																													) ?>)
					</a>
				</li>
			<?php } ?>
		</ol>
	</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
