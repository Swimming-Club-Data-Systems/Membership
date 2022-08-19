<?php

$db = app()->db;
$tenant = app()->tenant;

$query = $db->prepare("SELECT COUNT(*) FROM `posts` WHERE `ID` = ? AND Tenant = ?");
$query->execute([
	$id,
	$tenant->getId()
]);

if ($query->fetchColumn() == 0) {
	halt(404);
}

$data = [
	$_POST['content'],
	$_POST['title'],
	$_POST['excerpt'],
	trim($_POST['path'], " \t\n\r\0\x0B/"),
	$_POST['type'],
	$_POST['mime']
];

$data[] = $id;

try {
	$update = $db->prepare("UPDATE `posts` SET `Content` = ?, `Title` = ?, `Excerpt` = ?, `Path` = ?, `Type` = ?, `MIME` = ? WHERE `ID` = ?");
	$update->execute($data);
} catch (PDOException $e) {
	halt(500);
}


$_SESSION['TENANT-' . app()->tenant->getId()]['PostStatus'] = "Successfully updated";

header("Location: " . autoUrl("pages/" . $id));
