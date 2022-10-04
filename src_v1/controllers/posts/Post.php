<?php

$db = app()->db;
$tenant = app()->tenant;

$query = null;

$markdown = new ParsedownExtra();

// Safe mode is disabled during the transition to markdown
// $markdown->setSafeMode(true);

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
	$sql = "SELECT COUNT(*) FROM `members` WHERE `UserID` = ?";
	try {
		$query = $db->prepare($sql);
		$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
		if ($query->fetchColumn() == 0) {
			halt(404);
		}
	} catch (PDOException $e) {
		halt(500);
	}
}

if ($int) {
	$sql = "SELECT * FROM `posts` WHERE `ID` = ? AND Tenant = ?";
	try {
		$query = $db->prepare($sql);
		$query->execute([
			$id,
			$tenant->getId()
		]);
	} catch (PDOException $e) {
		halt(500);
	}
} else {
	$sql = "SELECT * FROM `posts` WHERE `Path` = ? AND Tenant = ?";
	try {
		$query = $db->prepare($sql);
		$query->execute([
			app()->request->args[1],
			$tenant->getId()
		]);
	} catch (PDOException $e) {
		halt(500);
	}
}
$row = $query->fetch(PDO::FETCH_ASSOC);

if (!$row) {
	halt(404);
}

if ($row['MIME'] != "text/html") {
	header('Content-Type: ' . $row['MIME']);
	echo $row['Content'];
	exit();
}

$use_white_background = true;
$pagetitle = htmlentities($row['Title']);

$container_classes = "";
if ($row['Type'] == "corporate_documentation") {
	// $container_classes .= "serif ";
}

$modified = new DateTime($row['Modified'], new DateTimeZone('UTC'));
$modified->setTimezone(new DateTimeZone('Europe/London'));

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/postsMenu.php";

?>

<!--<style>
#post-content p:last-child {
  margin-bottom: 0px;
}
</style>-->

<div class="bg-light mt-n3 py-4 mb-3">
	<div class="container-xl">
		<div class="row align-items-center">
			<div class="col-lg-8">
				<h1><?= htmlentities($row['Title']) ?></h1>
				<p class="lead mb-0">Last updated at <?= htmlspecialchars($modified->format('H:i \o\n j F Y')) ?></p>
			</div>
			<div class="ms-auto col-lg-auto">
				<div class="btn-group">
					<a href="<?= htmlspecialchars(autoUrl("pages/" . $row['ID'] . "/print.pdf")) ?>" class="btn btn-primary">
						Print document <i class="fa fa-print" aria-hidden="true"></i>
					</a>
					<a href="<?= htmlspecialchars(autoUrl("pages/" . $row['ID'] . "/edit")) ?>" class="btn btn-dark-l btn-outline-light-d">
						Edit
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="container <?= $container_classes ?>">

	<div class="row">
		<div class="col-lg-8">

			<div id="post-content" class="blog-main">
				<?= $markdown->text($row['Content']) ?>
			</div>
		</div>
	</div>
</div>

<?php
$footer = new \SCDS\Footer();
$footer->render();
