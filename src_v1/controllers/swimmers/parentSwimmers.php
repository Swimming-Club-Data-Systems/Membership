<?php

$db = app()->db;

$pagetitle = "My Members";
$userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];

include BASE_PATH . "views/header.php";

$getSwimmers = $db->prepare("SELECT MemberID id, MForename `first`, MSurname `last` FROM members WHERE members.UserID = ? ORDER BY `first` ASC, `last` ASC");
$getSwimmers->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);

?>

<div class="front-page mb-n3">
	<div class="container-xl">
		<h1>My members</h1>
		<?php if ($swimmer = $getSwimmers->fetch(PDO::FETCH_ASSOC)) { ?>
			<div class="row">
				<div class="col-md-8">
					<p class="lead">
						Members linked to your account
					</p>
					<p>
						Please remember that it is your own responsibility to also keep the Swim England Membership System up to date with personal details.
					</p>
				</div>
			</div>

			<div class="news-grid mb-4">
				<?php do { ?>
					<a href="<?= autoUrl("members/" . $swimmer['id']) ?>">
						<span class="mb-3">
							<span class="title mb-0">
								<?= htmlspecialchars(\SCDS\Formatting\Names::format($swimmer['first'], $swimmer['last'])) ?>
							</span>
							<span>
								<!-- Squads -->
							</span>
						</span>
						<span class="category">
							Member
						</span>
					</a>
				<?php } while ($swimmer = $getSwimmers->fetch(PDO::FETCH_ASSOC)); ?>
			</div>
		<?php } else { ?>
			<div class="row">
				<div class="col-md-8">
					<p class="lead">
						Members connected to your account will be shown here once you add them.
					</p>
				</div>
			</div>
		<?php } ?>

		<p>
			<a href="<?php echo autoUrl('myaccount/add-member'); ?>" class="btn btn-success">Link a member</a>
		</p>
	</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
