<?php

if (!app()->user->hasPermission('Admin')) halt(404);

if (!\SCDS\CSRF::verify()) halt(403);

$db = app()->db;
$tenant = app()->tenant;

// Get membership years
$today = new DateTime('now', new DateTimeZone('Europe/London'));

$renewal = \SCDS\Onboarding\Renewal::retrieve($id);

if (!$renewal) halt(404);

// $startDate = new DateTime($renewal->start, new DateTimeZone('Europe/London'));
// $endDate = new DateTime($renewal->end, new DateTimeZone('Europe/London'));
// $yearStart = new DateTime($renewal->yearStart, new DateTimeZone('Europe/London'));
// $yearEnd = new DateTime($renewal->yearEnd, new DateTimeZone('Europe/London'));

$stages = $renewal->defaultStages;
$stageNames = SCDS\Onboarding\Session::stagesOrder();
$memberStages = $renewal->defaultMemberStages;
$memberStageNames = SCDS\Onboarding\Member::stagesOrder();

$started = $renewal->isCurrent() || $renewal->isPast() || $renewal->isCreated();

try {

  $start = $renewal->start;
  $end = $renewal->end;

  // Validate date
  if (!($renewal->isCurrent() || $renewal->isPast())) {
    $start = new DateTime($_POST['start'], new DateTimeZone('Europe/London'));
  }
  $end = new DateTime($_POST['end'], new DateTimeZone('Europe/London'));

  $member = false;

  if (!($started)) {
    foreach ($stageNames as $stage => $desc) {
      if (!$stages->$stage->required_locked) {
        $stages->$stage->required = isset($_POST[$stage . '-main-check']) && bool($_POST[$stage . '-main-check']);
        if ($stages->$stage->required && $stage == 'member_forms') $member = true;
      }
    }

    if ($member) {
      foreach ($memberStageNames as $stage => $desc) {
        if (!$memberStages->$stage->required_locked) {
          $memberStages->$stage->required = isset($_POST[$stage . '-member-check']) && bool($_POST[$stage . '-member-check']);
        }
      }
    }
  }

  // Prepare to add the DB
  $insert = $db->prepare("UPDATE `renewalv2` SET `start` = ?, `end` = ?, `default_stages` = ?, `default_member_stages` = ? WHERE `id` = ?");
  $insert->execute([
    $start->format('Y-m-d'),
    $end->format('Y-m-d'),
    json_encode($stages),
    json_encode($memberStages),
    $id,
  ]);

  // If today, run job

  header("location: " . autoUrl("memberships/renewal/$id"));
} catch (Exception $e) {
  header("location: " . autoUrl("memberships/renewal/$id/edit"));
}
