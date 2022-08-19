<?php

$db = app()->db;
$tenant = app()->tenant->getId();

$verifyEntryId = $db->prepare("SELECT COUNT(*) FROM galaEntries INNER JOIN galas ON galas.GalaID = galaEntries.GalaID WHERE galaEntries.EntryID = ? AND galas.Tenant = ?");
$update = $db->prepare("UPDATE galaEntries SET EntryProcessed = ? WHERE EntryID = ?");
$markPaid = $db->prepare("UPDATE galaEntries SET Charged = ? WHERE EntryID = ?");

$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];
if ($access == "Committee" || $access == "Admin" || $access == "Coach" || $access == "Galas") {
	if ((isset($_POST["processedID"])) && (isset($_POST["clickedItemChecked"])) && (isset($_POST["verify"]))) {

		$id = $_POST["processedID"];

		// get the galaID parameter from POST
		$itemChecked = $_POST["clickedItemChecked"];
		$verify = $_POST["verify"];

		if ($verify == "markProcessed" /*&& $itemChecked != null*/) {
			if (strpos($id, 'processedEntry-') !== false) {
				// Okay it's what we want. Lets remove the "processedEntry" so we just have the ID left over
				$id = mb_substr($id, 15);
				// Verify entry id
				$verifyEntryId->execute([
					$id,
					$tenant,
				]);
				if ($verifyEntryId->fetchColumn() == 0) {
					halt(404);
				}
				if ($itemChecked == "true") {
					$update->execute([
						true,
						$id
					]);
				} else {
					$update->execute([
						0,
						$id
					]);
				}
			}
		} else if ($verify == "markPaid") {
			if (strpos($id, 'chargedEntry-') !== false) {
				// Okay it's what we want. Lets remove the "chargedEntry" so we just have the ID left over
				$id = mb_substr($id, 13);
				// Verify entry id
				$verifyEntryId->execute([
					$id,
					$tenant,
				]);
				if ($verifyEntryId->fetchColumn() == 0) {
					halt(404);
				}
				if ($itemChecked == "true") {
					$markPaid->execute([
						true,
						$id
					]);
				} else {
					$markPaid->execute([
						0,
						$id
					]);
				}
			}
		}
	}
}
