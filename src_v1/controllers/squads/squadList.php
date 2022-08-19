<?php


$db = app()->db;
$tenant = app()->tenant;

$squads = $db->prepare("SELECT SquadID, SquadName, SquadFee, SquadCoach FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, SquadName ASC");
$squads->execute([
	$tenant->getId()
]);

$getCoaches = $db->prepare("SELECT Forename fn, Surname sn FROM coaches INNER JOIN users ON coaches.User = users.UserID WHERE coaches.Squad = ? ORDER BY coaches.Type ASC, Forename ASC, Surname ASC");

$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];
$pagetitle = "Squads";
include BASE_PATH . "views/header.php";

?>

<div class="front-page mb-n3">
	<div class="container-xl">

		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item active" aria-current="page">Squads</li>
			</ol>
		</nav>

		<h1>Squads</h1>
		<p class="lead">Information about our squads and training groups</p>

		<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['DeleteSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['DeleteSuccess']) { ?>
			<div class="alert alert-success">We've deleted that squad. That action cannot be undone.</div>
		<?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['DeleteSuccess']);
		} ?>

		<?php if ($row = $squads->fetch(PDO::FETCH_ASSOC)) { ?>

			<div class="mb-4">
				<div class="news-grid">

					<?php
					do {
						$getCoaches->execute([$row['SquadID']]);
						$coaches = $getCoaches->fetchAll(PDO::FETCH_ASSOC);
					?>
						<a href="<?= htmlspecialchars(autoUrl("squads/" . $row['SquadID'])) ?>">
							<span class="mb-3">
								<span class="title mb-0">
									<?= htmlspecialchars($row['SquadName']) ?>
								</span>
								<span>
									<?php for ($i = 0; $i < min(sizeof($coaches), 3); $i++) { ?>
										<?= htmlspecialchars(\SCDS\Formatting\Names::format($coaches[$i]['fn'], $coaches[$i]['sn'])) ?><?php if ($i < min(sizeof($coaches), 3) - 1) { ?>, <?php } ?>
								<?php } ?>
								<?php if (sizeof($coaches) > 3) { ?> <em>and more</em><?php } ?>
								<?php if (sizeof($coaches) == 0) { ?>
									No assigned coaches
								<?php } ?>
								</span>
							</span>
							<span class="category">
								&pound;<?= number_format($row['SquadFee'], 2) ?> per month
							</span>
						</a>
					<?php
					} while ($row = $squads->fetch(PDO::FETCH_ASSOC));
					?>

				</div>
			</div>

		<?php } else { ?>
			<div class="row">
				<div class="col-lg-8">
					<div class="alert alert-info">
						<p class="mb-0">
							<strong>No squads to display</strong>
						</p>
					</div>
				</div>
			</div>
		<?php } ?>

		<?php if ($access == "Admin") { ?>
			<p>
				<a href="<?= autoUrl("squads/new") ?>" class="btn btn-success">Add a Squad <span class="fa fa-chevron-right"></span></a>
			</p>
		<?php } ?>
	</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
