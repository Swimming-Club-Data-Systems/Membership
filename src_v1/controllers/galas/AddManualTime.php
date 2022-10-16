<?php

$db = app()->db;
$tenant = app()->tenant;

$swimsArray = ['25Free', '50Free', '100Free', '200Free', '400Free', '800Free', '1500Free', '25Back', '50Back', '100Back', '200Back', '25Breast', '50Breast', '100Breast', '200Breast', '25Fly', '50Fly', '100Fly', '200Fly', '100IM', '150IM', '200IM', '400IM',];
$swimsTextArray = ['25&nbsp;Free', '50&nbsp;Free', '100&nbsp;Free', '200&nbsp;Free', '400&nbsp;Free', '800&nbsp;Free', '1500&nbsp;Free', '25&nbsp;Back', '50&nbsp;Back', '100&nbsp;Back', '200&nbsp;Back', '25&nbsp;Breast', '50&nbsp;Breast', '100&nbsp;Breast', '200&nbsp;Breast', '25&nbsp;Fly', '50&nbsp;Fly', '100&nbsp;Fly', '200&nbsp;Fly', '100&nbsp;IM', '150&nbsp;IM', '200&nbsp;IM', '400&nbsp;IM',];
$swimsTimeArray = ['25FreeTime', '50FreeTime', '100FreeTime', '200FreeTime', '400FreeTime', '800FreeTime', '1500FreeTime', '25BackTime', '50BackTime', '100BackTime', '200BackTime', '25BreastTime', '50BreastTime', '100BreastTime', '200BreastTime', '25FlyTime', '50FlyTime', '100FlyTime', '200FlyTime', '100IMTime', '150IMTime', '200IMTime', '400IMTime',];

$sql = $db->prepare("SELECT * FROM ((`galaEntries` INNER JOIN `members` ON `members`.`MemberID` = `galaEntries`.`MemberID`) INNER JOIN `galas` ON galaEntries.GalaID = galas.GalaID) WHERE members.Tenant = ? AND `EntryID` = ?;");
$sql->execute([
	$tenant->getId(),
	$id
]);

$row = $sql->fetch(PDO::FETCH_ASSOC);

$locked = "";
$processed = false;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && bool($row['EntryProcessed'])) {
	$locked = " disabled ";
	$processed = true;
}

if ($row == null) {
	halt(404);
}

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && $row['UserID'] != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) {
	halt(404);
}

$member = $row['MemberID'];

$type = null;
if ($row['CourseLength'] == "SHORT") {
	$type = "SCPB";
} else {
	$type = "LCPB";
}

$pagetitle = "Add manual times for " . htmlspecialchars($row['MForename'][0] . $row['MSurname'][0] . ' at ' . $row['GalaName']);
include BASE_PATH . 'views/header.php';

$course = 'short';
$altCourse = 'long';
if ($row['CourseLength'] == 'LONG') {
	$course = 'long';
	$altCourse = 'short';
}

?>

<div class="bg-light mt-n3 py-3 mb-3">
	<div class="container-xl">

		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= autoUrl("galas") ?>">Galas</a></li>
				<li class="breadcrumb-item"><a href="<?= autoUrl("galas/entries") ?>">Entries</a></li>
				<li class="breadcrumb-item"><a href="<?= autoUrl("galas/entries/" . $row['EntryID']) ?>"><?= htmlspecialchars(mb_substr($row["MForename"], 0, 1, 'utf-8') . mb_substr($row["MSurname"], 0, 1, 'utf-8')) ?> (<?= htmlspecialchars($row['GalaName']) ?>)</a></li>
				<li class="breadcrumb-item active" aria-current="page">Times</li>
			</ol>
		</nav>


		<h1>Add Manual Times for <?= htmlspecialchars(\SCDS\Formatting\Names::format($row['MForename'], $row['MSurname'])) ?></h1>
		<p class="lead mb-0">
			<?= htmlspecialchars($row['GalaName']) ?>
		</p>
	</div>
</div>

<div class="container-xl">
	<div class="row">
		<div class="col-lg-8">

			<?php if ($processed) { ?>
				<div class="alert alert-warning">
					<p class="mb-0">
						<strong>This entry has already been processed.</strong>
					</p>
					<p class="mb-0">
						As a result you are no longer able to edit your entry times. Please speak to your gala coordinator if you need to make changes.
					</p>
					<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateError'])) { ?>
						<p class="mb-0 mt-3">
							<?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateError']) ?>
						</p>
					<?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateError']);
					} ?>
				</div>
			<?php } else if (bool($row['EntryProcessed'])) { ?>
				<div class="alert alert-warning">
					<p class="mb-0">
						<strong>This entry has already been processed.</strong>
					</p>
					<p class="mb-0">
						You may make changes, but any changes you make may not necessarily be submitted to the gala host.
					</p>
					<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateError'])) { ?>
						<p class="mb-0 mt-3">
							<?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateError']) ?>
						</p>
					<?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateError']);
					} ?>
				</div>
			<?php } ?>

			<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateSuccess'])) {
				if ($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateSuccess']) { ?>
					<div class="alert alert-success">
						<p class="mb-0">
							<strong>
								Your manual times have been saved successfully.
							</strong>
						</p>
					</div>
				<?php } else { ?>
					<div class="alert alert-danger">
						<p class="mb-0">
							<strong>
								We were unable to save your manual times.
							</strong>
						</p>
						<p class="mb-0">
							Please try again. If the issue persists, contact support.
						</p>
					</div>
			<?php }
				unset($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateSuccess']);
			} ?>

			<p>
				Please enter <?= $course ?> course times. If you have no <?= $course ?> course time for an event but do have a <?= $altCourse ?> course time, please <a href="<?= autoUrl("timeconverter") ?>" target="_blank">use our online time converter (opens in new tab)</a> to convert it from <?= $altCourse ?> course to <?= $course ?> course.
			</p>

			<p>
				If you do not have any short course or long course times for an event, leave it blank.
			</p>

			<form method="post" class="">
				<h2>Swims</h2>
				<p class="lead">
					These are the events you entered.
				</p>

				<div>
					<?php for ($i = 0; $i < sizeof($swimsArray); $i++) { ?>
						<?php if ($row[$swimsArray[$i]] == 1) { ?>
							<div>
								<div class="cell">
									<strong class="d-block">
										<label>
											<?= $swimsTextArray[$i] ?>
										</label>
									</strong>
									<?php
									$matches = $mins = $secs = $hunds = "";
									if ($row[$swimsTimeArray[$i]] != "") {
										if (preg_match('/([0-9]+)\:([0-9]{0,2})\.([0-9]{0,2})/', $row[$swimsTimeArray[$i]], $matches)) {
											if (isset($matches[1]) && $matches[1] != 0) {
												$mins = $matches[1];
											}
											if (isset($matches[2]) && $matches[2] != 0) {
												$secs = $matches[2];
											}
											if (isset($matches[3]) && $matches[3] != 0) {
												$hunds = $matches[3];
											}
										}
									}
									?>
									<div class="mb-0 mt-2">
										<div class="row g-2">
											<div class="col">
												<div class="form-floating">
													<input type="number" class="form-control" placeholder="Minutes" name="<?= $swimsTimeArray[$i] ?>Mins" id="<?= $swimsTimeArray[$i] ?>Mins" autocomplete="off" pattern="[0-9]*" inputmode="numeric" min="0" value="<?= htmlspecialchars($mins) ?>" <?= $locked ?>>
													<label for="<?= $swimsTimeArray[$i] ?>Mins">Minutes</label>
												</div>
											</div>
											<div class="col">
												<div class="form-floating">
													<input type="number" class="form-control" placeholder="Seconds" name="<?= $swimsTimeArray[$i] ?>Secs" id="<?= $swimsTimeArray[$i] ?>Secs" autocomplete="off" pattern="[0-9]*" inputmode="numeric" min="0" max="59" value="<?= htmlspecialchars($secs) ?>" <?= $locked ?>>
													<label for="<?= $swimsTimeArray[$i] ?>Secs">Seconds</label>
												</div>
											</div>
											<div class="col">
												<div class="form-floating">
													<input type="number" class="form-control" placeholder="Hundreds" name="<?= $swimsTimeArray[$i] ?>Hunds" id="<?= $swimsTimeArray[$i] ?>Hunds" autocomplete="off" pattern="[0-9]*" inputmode="numeric" min="0" max="99" value="<?= htmlspecialchars($hunds) ?>" <?= $locked ?>>
													<label for="<?= $swimsTimeArray[$i] ?>Hunds">Hundreds</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>
					<?php } ?>
				</div>

				<p>
					<button class="btn btn-success" type="submit" <?= $locked ?>>Save</button>
				</p>
			</form>
		</div>
	</div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
