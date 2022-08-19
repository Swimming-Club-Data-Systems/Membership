<?php

$db = app()->db;
$tenant = app()->tenant;

$name = $price = $errorMessage = null;
$errorState = false;

if (isset($_POST['name']) && $_POST['name'] != null && $_POST['name'] != "") {
	$name =	trim($_POST['name']);
} else {
	$errorState = true;
	$errorMessage .= "<li>There was a problem with that name</li>";
}

if (isset($_POST['price']) && $_POST['price'] != null && $_POST['price'] != "") {
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
    $update = $db->prepare("UPDATE extras SET ExtraName = ?, ExtraFee = ?, `Type` = ? WHERE ExtraID = ? AND Tenant = ?");
    $update->execute([
			$name,
			$price,
			$type,
			$id,
			$tenant->getId()
		]);
    header("Location: " . autoUrl("payments/extrafees/" . $id));
	} catch (Exception $e) {
		$errorState = true;
		$errorMessage .= "<li>Unable to edit item in database</li>";
	}
}

if ($errorState) {
	$_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'] = '
	<div class="alert alert-danger">
	Something went wrong and we couldn\'t carry out that operation
	<ul class="mb-0">' . $errorMessage . '</ul></div>';
	header("Location: " . autoUrl("payments/extrafees/" . $id . "/edit"));
}
