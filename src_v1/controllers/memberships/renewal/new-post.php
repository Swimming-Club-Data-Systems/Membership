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

  $yearClub = $yearNgb = null;

  if ($_POST['year-ngb'] != 'NONE') {

    // Validate year
    $getYears = $db->prepare("SELECT ID FROM `membershipYear` WHERE `Tenant` = ? AND `ID` = ?");
    $getYears->execute([
      $tenant->getId(),
      $_POST['year-ngb'],
    ]);
    $yearNgb = $getYears->fetchColumn();
    if (!$yearNgb) throw new Exception('Invalid membership year');
  }

  if ($_POST['year-club'] != 'NONE') {

    // Validate year
    $getYears = $db->prepare("SELECT ID FROM `membershipYear` WHERE `Tenant` = ? AND `ID` = ?");
    $getYears->execute([
      $tenant->getId(),
      $_POST['year-club'],
    ]);
    $yearClub = $getYears->fetchColumn();
    if (!$yearClub) throw new Exception('Invalid membership year');
  }

  if (!$yearClub && !$yearNgb) throw new Exception('No membership year selected');

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

  $paymentTypes = [];
  if (isset($_POST['payment-card']) && bool($_POST['payment-card'])) {
    $paymentTypes[] = 'card';
  }
  if (isset($_POST['payment-direct-debit']) && bool($_POST['payment-direct-debit'])) {
    $paymentTypes[] = 'dd';
  }

  $metadata = [];
  $metadata['supported_payment_types'] = $paymentTypes;

  $clubDate = null;
  $ngbDate = null;

  if (isset($_POST['use-custom-bill-dates'])) {
    // Add custom bill dates to metadata

    if ($yearClub && isset($_POST['dd-club-bills-date'])) {
      try {
        $clubDate = (new DateTime($_POST['dd-club-bills-date'], new DateTimeZone('Europe/London')))->format('Y-m-d');
      } catch (Exception $e) {
      }
    }

    if ($yearNgb && isset($_POST['dd-ngb-bills-date'])) {
      try {
        $ngbDate = (new DateTime($_POST['dd-ngb-bills-date'], new DateTimeZone('Europe/London')))->format('Y-m-d');
      } catch (Exception $e) {
      }
    }
  }

  $metadata['custom_direct_debit_bill_dates'] = [
    'club' => $clubDate,
    'ngb' => $ngbDate,
  ];

  // Prepare to add the DB
  $insert = $db->prepare("INSERT INTO `renewalv2` (`id`, `club_year`, `ngb_year`, `start`, `end`, `default_stages`, `default_member_stages`, `metadata`, `Tenant`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $insert->execute([
    $id,
    $yearClub,
    $yearNgb,
    $start->format('Y-m-d'),
    $end->format('Y-m-d'),
    json_encode($stages),
    json_encode($memberStages),
    json_encode([
      'supported_payment_types' => $paymentTypes
    ]),
    $tenant->getId(),
  ]);

  // If today, run job

  header("location: " . autoUrl("memberships/renewal/$id"));
} catch (Exception $e) {
  reportError($e);
  header("location: " . autoUrl("memberships/renewal/new"));
}
