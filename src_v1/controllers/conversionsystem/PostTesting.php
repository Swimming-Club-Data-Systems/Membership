<?php

use CLSASC\EquivalentTime\EquivalentTime;

try {
	if (!\SCDS\CSRF::verify()) {
		throw new Exception();
	}

	$mins = $secs = $hunds = 0;

	if (isset($_POST['mins'])) {
		$mins = (int) $_POST['mins'];
	}
	if (isset($_POST['secs'])) {
		$secs = (int) $_POST['secs'];
	}
	if (isset($_POST['hunds'])) {
		$hunds = (int) $_POST['hunds'];
	}

	$time = (double) 60*$mins + $secs + ($hunds/100);

	$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Mins']		= $mins;
	$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Secs']		= $secs;
	$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Hunds']	= $hunds;

	try {
		$time = new EquivalentTime($_POST['source'], $_POST['event'], $time);
		$time->setOutputAsString();
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Error'] = false;
	} catch (\Exception $e) {
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Error'] = true;
		throw new Exception();
	}

	if ($_POST['source'] == "50m") {
		try {
			$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-25m'] = $time->getConversion("25m") . "0";
			$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-25m-fail'] = false;
		} catch (\Exception $e) {
			$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-25m-fail'] = true;
		}
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-50m'] = htmlentities(sprintf('%02d', $mins)) . ":" . htmlentities(sprintf('%02d', $secs)) . ":" . htmlentities(sprintf('%02d', $hunds)) . " - Origin";
	} else {
		try {
			$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-50m'] = $time->getConversion("50m") . "0";
			$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-50m-fail'] = false;
		} catch (\Exception $e) {
			$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-50m-fail'] = true;
		}
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-25m'] = htmlentities(sprintf('%02d', $mins)) . ":" . htmlentities(sprintf('%02d', $secs)) . ":" . htmlentities(sprintf('%02d', $hunds)) . " - Origin";
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-25m-fail'] = false;
	}

	try {
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-33m'] = $time->getConversion("33 1/3m") . "0";
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-33m-fail'] = false;
	} catch (\Exception $e) {
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-33m-fail'] = true;
	}

	try {
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-20m'] = $time->getConversion("20m") . "0";
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-20m-fail'] = false;
	} catch (\Exception $e) {
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-20m-fail'] = true;
	}

	try {
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-36y'] = $time->getConversion("36 2/3y") . "0";
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-36y-fail'] = false;
	} catch (\Exception $e) {
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-36y-fail'] = true;
	}

	try {
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-27y'] = $time->getConversion("27 1/2y") . "0";
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-27y-fail'] = false;
	} catch (\Exception $e) {
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-27y-fail'] = true;
	}

	try {
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-33y'] = $time->getConversion("33 1/3y") . "0";
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-33y-fail'] = false;
	} catch (\Exception $e) {
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-33y-fail'] = true;
	}

	try {
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-25y'] = $time->getConversion("25y") . "0";
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-25y-fail'] = false;
	} catch (\Exception $e) {
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-25y-fail'] = true;
	}

	try {
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-20y'] = $time->getConversion("20y") . "0";
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-20y-fail'] = false;
	} catch (\Exception $e) {
		$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-20y-fail'] = true;
	}

	$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Event'] = $_POST['event'];
} catch (Exception $e) {

}

header("Location: " . autoUrl("time-converter"));
