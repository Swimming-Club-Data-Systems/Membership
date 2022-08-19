<?php

$db = app()->db;
$tenant = app()->tenant;

$name = $price = $errorMessage = null;
$errorState = false;

if ($_POST['name'] != null && $_POST['name'] != "") {
	$name =	trim($_POST['name']);
} else {
	$errorState = true;
	$errorMessage .= "<li>There was a problem with that name</li>";
}

if ($_POST['price'] != null && $_POST['price'] != "") {
	$price = number_format($_POST['price'],2,'.','');
	if ($_POST['price'] < 0) {
		$errorState = true;
		$errorMessage .= "<li>The price was negative. Set a positive (or 0 value) price and choose payment or credit/refund.</li>";
	}
} else {
	$errorState = true;
	$errorMessage .= "<li>There was a problem with that price</li>";
}

$type = 'Payment';
if (isset($_POST['pay-credit-type']) && ($_POST['pay-credit-type'] == 'Payment' || $_POST['pay-credit-type'] == 'Refund')) {
	if ($_POST['pay-credit-type'] == 'Refund') {
		$type = 'Refund';
	}
} else {
	$errorState = true;
	$errorMessage .= "<li>There was a problem with the type of this item.</li>";
}

if (!$errorState) {
  try {
    $insert = $db->prepare("INSERT INTO `extras` (`ExtraName`, `ExtraFee`, `Type`, Tenant) VALUES (?, ?, ?, ?)");
		$insert->execute([
			$name,
			$price,
			$type,
			$tenant->getId()
		]);
		$id = $db->lastInsertId();
    header("Location: " . autoUrl("payments/extrafees/" . $id));
  } catch (Exception $e) {
    $errorState = true;
		$errorMessage .= "<li>Unable to add to database</li>";
  }
}

if ($errorState) {
	$_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'] = '
	<div class="alert alert-danger">
	Something went wrong and we couldn\'t carry out that operation
	<ul class="mb-0">' . $errorMessage . '</ul></div>';
	header("Location: " . autoUrl("payments/extrafees/new"));
}
