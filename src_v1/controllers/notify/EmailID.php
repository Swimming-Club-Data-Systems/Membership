<?php
try {
$db = app()->db;
$tenant = app()->tenant;

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];

$sql = $db->prepare("SELECT Forename, Surname, notify.Subject PSubject, notifyHistory.Subject HSubject, notify.Message PMessage, notifyHistory.Message HMessage FROM ((`notify` LEFT JOIN notifyHistory ON notify.MessageID = notifyHistory.ID) INNER JOIN `users` ON notify.UserID = users.UserID) WHERE `EmailID` = ? AND users.Tenant = ?");
$sql->execute([$id, $tenant->getId()]);

$row = $sql->fetch(PDO::FETCH_ASSOC);

if ($row == null) {
	halt(404);
}

$subject = $row['PSubject'];
if ($row['PSubject'] == null) {
	$subject = $row['HSubject'];
}

$message = $row['PMessage'];
if ($row['PMessage'] == null) {
	$message = $row['HMessage'];
}

$pagetitle = htmlspecialchars($subject . " - " . $row['Forename'] . " " . $row['Surname']);

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/notifyMenu.php";

 ?>

<div class="container-xl">
	<h1><strong><?=htmlspecialchars($subject)?></strong></h1>
	<p class="lead">Sent to <?=htmlspecialchars($row['Forename'] . " " . $row['Surname'])?></p>

	<div class="card">
		<div class="card-body">
			<h2 class="card-title">Message</h2>
			<?=$message?>
		</div>
	</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
} catch (Exception $e) {
	pre($e);
}