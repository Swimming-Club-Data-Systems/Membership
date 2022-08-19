<?php

use Respect\Validation\Validator as v;
$db = app()->db;
$currentUser = app()->user;

$twofa = false;
if ($currentUser->getUserBooleanOption('Is2FA')) {
	$twofa = true;
}

$trackers = true;
if ($currentUser->getUserBooleanOption('DisableTrackers')) {
	$trackers = false;
}

$genericTheme = true;
if ($currentUser->getUserBooleanOption('UsesGenericTheme')) {
	$genericTheme = false;
}

$galaDDOptOut = false;
if ($currentUser->getUserBooleanOption('GalaDirectDebitOptOut')) {
	$galaDDOptOut = true;
}

$betas = true;
if ($currentUser->getUserBooleanOption('EnableBeta')) {
	$betas = false;
}

if ($_POST['2FA'] == "1") {
  $currentUser->setUserOption("Is2FA", "1");
} else {
  $currentUser->setUserOption("Is2FA", "0");
}

if ($_POST['generic-theme'] == "1") {
  $currentUser->setUserOption("UsesGenericTheme", "1");
} else {
  $currentUser->setUserOption("UsesGenericTheme", "0");
}

if ($_POST['beta-features'] == "1") {
  $currentUser->setUserOption("EnableBeta", "1");
} else {
  $currentUser->setUserOption("EnableBeta", "0");
}

if ($_POST['tracking-cookies'] == "1") {
  $currentUser->setUserOption("DisableTrackers", "1");
} else {
  $currentUser->setUserOption("DisableTrackers", "0");
}

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
  if ($_POST['gala-dd-opt-out'] == "1") {
    $currentUser->setUserOption("GalaDirectDebitOptOut", "1");
  } else {
    $currentUser->setUserOption("GalaDirectDebitOptOut", "0");
  }
}

$_SESSION['TENANT-' . app()->tenant->getId()]['DisableTrackers'] = $currentUser->getUserBooleanOption('DisableTrackers');

if ($twofa != ($_POST['2FA'] == "1") || $betas != ($_POST['beta-features'] == "1") || $trackers != ($_POST['tracking-cookies'] == "1") || $genericTheme != $currentUser->getUserBooleanOption('UsesGenericTheme') || $galaDDOptOut != ($_POST['gala-dd-opt-out'] == "1")) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate'] = true;
}

header("Location: " . autoUrl("my-account/general"));
