<?php

$db = app()->db;
$tenant = app()->tenant;

$sql = null;

if ($_POST['squadID'] == 'allSquads') {
	$sql = "SELECT DISTINCT `Mobile` FROM `users` INNER JOIN `members` ON `members`.`UserID` = `users`.`UserID` WHERE members.Tenant = ? AND `MobileComms` = 1";
} else {
	$sql = "SELECT DISTINCT `Mobile` FROM ((`users` INNER JOIN `members` ON `members`.`UserID` = `users`.`UserID`) INNER JOIN squadMembers ON members.MemberID = squadMembers.Member) WHERE members.Tenant = ? AND squadMembers.Squad = ? AND `MobileComms` = 1";
}

try {
	$query = $db->prepare($sql);
	if ($_POST['squadID'] == 'allSquads') {
		$query->execute([
			$tenant->getId()
		]);
	} else {
		$query->execute([
			$tenant->getId(),
			$_POST['squadID']
		]);
	}
} catch (PDOException $e) {
	halt(500);
}

$row = $query->fetchAll(PDO::FETCH_ASSOC);

if (!$row) {
	echo "There are no phone numbers available for this squad.";
} else {
	for ($i = 0; $i < sizeof($row); $i++) {
		if ($i < sizeof($row)-1) {
			echo htmlspecialchars($row[$i]['Mobile']) . ", ";
		} else {
			echo htmlspecialchars($row[$i]['Mobile']);
		}
	}
}
