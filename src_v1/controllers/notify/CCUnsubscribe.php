<?php

$db = app()->db;

$id = hexdec($id);
$hash_test = hash('sha256', $id);

if ($hash != $hash_test) {
  halt(404);
}

try {
	$query = $db->prepare("SELECT COUNT(*) FROM `notifyAdditionalEmails` WHERE `ID` = ?");
	$query->execute([$id]);
} catch (Exception $e) {
	halt(500);
}

if ($query->fetchColumn() != 1) {
	//Do something
	halt(404);
}

try {
	$query = $db->prepare("DELETE FROM `notifyAdditionalEmails` WHERE `ID` = ?");
	$query->execute([$id]);
} catch (Exception $e) {
	halt(500);
}

$pagetitle = "Notify Unsubscribe";
include BASE_PATH . "views/header.php";?>

<div class="container-xl">
	<h1>Successfully Unsubscribed</h1>
	<p>You will no longer receive emails from this list.</p>
	<p>
		For further help and support with emails from <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>, visit
		our <a href="<?=autoUrl("notify")?>">Notify Help Centre</a>.
	</p>
	<p>
		Notify by <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>
	</p>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
