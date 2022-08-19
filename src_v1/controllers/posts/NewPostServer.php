<?php

$db = app()->db;
$tenant = app()->tenant;

$date = $_POST['date'];
if ($date == "") {
	$date = new DateTime('now', new DateTimeZone('UTC'));
} else {
	$date = new DateTime($_POST['date'], new DateTimeZone('UTC'));
}

$data = [
	$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
	$date->format("Y-m-d H:i:s"),
	$_POST['content'],
	$_POST['title'],
	$_POST['excerpt'],
	trim($_POST['path'], " \t\n\r\0\x0B/"),
	$_POST['type'],
	$_POST['mime'],
	$tenant->getId()
];

try {
	$db->prepare("INSERT INTO `posts` (`Author`, `Date`, `Content`, `Title`, `Excerpt`, `Path`, `Type`, `MIME`, `Tenant`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute($data);
} catch (PDOException $e) {
	halt(500);
}

$id = $db->lastInsertId();

$_SESSION['TENANT-' . app()->tenant->getId()]['PostStatus'] = "Successfully added";

header("Location: " . autoUrl("pages/" . $id));
