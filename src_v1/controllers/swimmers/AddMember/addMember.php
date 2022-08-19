<?php

$db = app()->db;
$tenant = app()->tenant;

$getClasses = $db->prepare("SELECT `ID`, `Name` FROM `clubMembershipClasses` WHERE `Tenant` = ? AND `Type` = 'club' ORDER BY `Name` ASC");
$getClasses->execute([
	$tenant->getId()
]);
$class = $getClasses->fetch(PDO::FETCH_ASSOC);

$getNGBCategories = $db->prepare("SELECT `ID`, `Name` FROM `clubMembershipClasses` WHERE `Tenant` = ? AND `Type` = 'national_governing_body' ORDER BY `Name` ASC");
$getNGBCategories->execute([
	$tenant->getId()
]);
$ngbCategory = $getNGBCategories->fetch(PDO::FETCH_ASSOC);

$today = new DateTime('now', new DateTimeZone('Europe/London'));

$squads = $db->prepare("SELECT SquadName, SquadID FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, SquadName ASC");
$squads->execute([
	$tenant->getId(),
]);

$pagetitle = "Add a member";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/swimmersMenu.php"; ?>

<div class="bg-light mt-n3 py-3 mb-3">
	<div class="container-xl">

		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('members')) ?>">Members</a></li>
				<li class="breadcrumb-item active" aria-current="page">New</li>
			</ol>
		</nav>

		<div class="row align-items-center">
			<div class="col">
				<h1>
					Add a member
				</h1>
				<p class="lead mb-0">
					Add a new club member to the system
				</p>
			</div>
		</div>
	</div>
</div>

<div class="container-xl">
	<div class="row">
		<div class="col col-md-8">
			<?php
			if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) { ?>
				<?= $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'] ?>
			<?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
			} ?>
			<form method="post" class="needs-validation" novalidate>
				<div class="row g-2">
					<div class="col-sm-4">
						<div class="mb-3">
							<label class="form-label" for="forename">Forename</label>
							<input type="text" class="form-control" id="forename" name="forename" placeholder="Enter a forename" required>
							<div class="invalid-feedback">
								Please provide a forename.
							</div>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="mb-3">
							<label class="form-label" for="middlenames">Middle Names</label>
							<input type="text" class="form-control" id="middlenames" name="middlenames" placeholder="Enter a middlename">
						</div>
					</div>
					<div class="col-sm-4">
						<div class="mb-3">
							<label class="form-label" for="surname">Surname</label>
							<input type="text" class="form-control" id="surname" name="surname" placeholder="Enter a surname" required>
							<div class="invalid-feedback">
								Please provide a surname.
							</div>
						</div>
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="datebirth">Date of Birth</label>
					<input type="date" class="form-control" id="datebirth" name="datebirth" pattern="[0-9]{4}-[0-1]{1}[0-9]{1}-[0-3]{1}[0-9]{1}" placeholder="YYYY-MM-DD" required max="<?= htmlspecialchars($today->format('Y-m-d')) ?>" value="<?= htmlspecialchars($today->format('Y-m-d')) ?>">
					<div class="invalid-feedback">
						Please provide a valid date of birth, which is not in the future.
					</div>
				</div>
				<div class="row g-2">
					<div class="col-sm-6">
						<div class="mb-3">
							<label class="form-label" for="asa"><?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> Registration Number</label>
							<input type="test" class="form-control" id="asa" name="asa" aria-describedby="asaHelp" placeholder="<?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> Registration Numer">
							<small id="asaHelp" class="form-text text-muted">If a new member does not yet have a <?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> registration number, leave this blank.</small>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="mb-3">
							<label class="form-label" for="ngb-cat"><?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> Membership Category</label>
							<select class="form-select overflow-hidden" id="ngb-cat" name="ngb-cat" placeholder="Select" required>
								<option value="none">No <?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> membership</option>
								<?php do { ?>
									<option value="<?= htmlspecialchars($ngbCategory['ID']) ?>" <?php if ($tenant->getKey('DEFAULT_NGB_MEMBERSHIP_CLASS') == $ngbCategory['ID']) { ?>selected<?php } ?>><?= htmlspecialchars($ngbCategory['Name']) ?></option>
								<?php } while ($ngbCategory = $getNGBCategories->fetch(PDO::FETCH_ASSOC)); ?>
							</select>
						</div>
					</div>
				</div>

				<div class="mb-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" id="clubpays" name="clubpays" value="1" aria-describedby="cphelp">
						<label class="form-check-label" for="clubpays">Club pays <?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> Membership fees?</label>
					</div>
					<small id="cphelp" class="form-text text-muted">Tick the box if this swimmer will not pay any <?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> fees.</small>
				</div>

				<div class="mb-3">
					<label class="form-label" for="sex">Sex</label>
					<select class="form-select" id="sex" name="sex" placeholder="Select" required>
						<option value="Male">Male</option>
						<option value="Female">Female</option>
					</select>
				</div>

				<!-- Squads -->
				<?php if ($squad = $squads->fetch(PDO::FETCH_ASSOC)) { ?>
					<p class="mb-2">
						Select <span id="member-name">member's</span> squads.
					</p>

					<div class="row">
						<?php do { ?>
							<div class="col-6 col-md-4 col-lg-3 mb-2">
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="squad-check-<?= htmlspecialchars($squad['SquadID']) ?>" name="squad-<?= htmlspecialchars($squad['SquadID']) ?>" value="1">
									<label class="form-check-label" for="squad-check-<?= htmlspecialchars($squad['SquadID']) ?>"><?= htmlspecialchars($squad['SquadName']) ?></label>
								</div>
							</div>
						<?php } while ($squad = $squads->fetch(PDO::FETCH_ASSOC)); ?>
					</div>

					<p class="text-muted mt-n2">
						<small>If this new member is not a member of any squads, you don't need to select any.</small>
					</p>
				<?php } ?>

				<div class="mb-3">
					<label class="form-label" for="membership-class">Select club membership class</label>
					<select class="form-select" id="membership-class" name="membership-class" required>
						<option value="" selected disabled>Choose a club membership class</option>
						<?php do { ?>
							<option value="<?= htmlspecialchars($class['ID']) ?>" <?php if ($tenant->getKey('DEFAULT_MEMBERSHIP_CLASS') == $class['ID']) { ?>selected<?php } ?>><?= htmlspecialchars($class['Name']) ?></option>
						<?php } while ($class = $getClasses->fetch(PDO::FETCH_ASSOC)); ?>
					</select>
				</div>

				<div class="mb-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" id="clubmemb" name="clubmemb" value="1" aria-describedby="clubmembhelp">
						<label class="form-check-label" for="clubmemb">Club pays Club Membership fees?</label>
					</div>
					<small id="clubmembhelp" class="form-text text-muted">Tick the box if this swimmer will not pay any annual club membership fees.</small>
				</div>

				<?= SCDS\CSRF::write() ?>
				<button type="submit" class="btn btn-success">Add Member</button>

			</form>

		</div>
	</div>
</div>

<?php
$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
