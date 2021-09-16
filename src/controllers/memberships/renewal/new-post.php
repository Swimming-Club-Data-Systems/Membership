<?php

use Ramsey\Uuid\Uuid;

if (!app()->user->hasPermission('Admin')) halt(404);

if (!\SCDS\CSRF::verify()) halt(403);

$db = app()->db;
$tenant = app()->tenant;

$stages = SCDS\Onboarding\Session::getDefaultRenewalStages();
$stageNames = SCDS\Onboarding\Session::stagesOrder();
$memberStages = SCDS\Onboarding\Member::getDefaultStages();
$memberStageNames = SCDS\Onboarding\Member::stagesOrder();

try {

  // Validate year
  $getYears = $db->prepare("SELECT ID FROM `membershipYear` WHERE `Tenant` = ? AND `ID` = ?");
  $getYears->execute([
    $tenant->getId(),
    $_POST['year'],
  ]);
  $year = $getYears->fetchColumn();

  if (!$year) throw new Exception('Invalid membership year');

  // Validate date
  $start = new DateTime($_POST['start'], new DateTimeZone('Europe/London'));
  $end = new DateTime($_POST['end'], new DateTimeZone('Europe/London'));

  $member = false;

  foreach ($stages as $stage => $details) {
    if (!$stages[$stage]['required_locked']) {
      $stages[$stage]['required'] = isset($_POST[$stage . '-main-check']) && bool($_POST[$stage . '-main-check']);
      if ($stages[$stage]['required'] && $stage == 'member_forms') $member = true;
    }
  }

  if ($member) {
    foreach ($memberStages as $stage => $details) {
      $memberStages[$stage]['required'] = isset($_POST[$stage . '-member-check']) && bool($_POST[$stage . '-member-check']);
    }
  }

  $id = Uuid::uuid4();

  // Prepare to add the DB
  $insert = $db->prepare("INSERT INTO `renewalv2` (`id`, `year`, `start`, `end`, `default_stages`, `default_member_stages`, `metadata`, `Tenant`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $insert->execute([
    $id,
    $_POST['year'],
    $start->format('Y-m-d'),
    $end->format('Y-m-d'),
    json_encode($stages),
    json_encode($memberStages),
    json_encode([]),
    $tenant->getId(),
  ]);

  // If today, run job

  header("location: " . autoUrl("memberships/renewal/$id"));
} catch (Exception $e) {
  header("location: " . autoUrl("memberships/renewal/new"));
}
