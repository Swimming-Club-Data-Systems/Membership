<?php

require "GoCardlessSetup.php";

if (!isset($mandate) || $mandate == "") {
  halt(400);
}

$db = app()->db;
$tenant = app()->tenant;

$checkDetails = $db->prepare("SELECT paymentMandates.UserID FROM `paymentMandates` INNER JOIN users ON paymentMandates.UserID = users.UserID WHERE users.Tenant = ? AND `Mandate` = ?");
$checkDetails->execute([
  $tenant->getId(),
  $mandate
]);

$userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];

$mandateUser = $checkDetails->fetchColumn();

if ($mandateUser == null) {
  halt(404);
}

if ($mandateUser != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'] && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Admin") {
  halt(404);
}

$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];

try {
  $return = $client->mandatePdfs()->create([
    "params" => ["links" => ["mandate" => $mandate]]
  ]);

  header("Location: " . $return->url);
} catch (Exception $e) {
  halt(500);
}