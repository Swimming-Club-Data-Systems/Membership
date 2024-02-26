<?php

// Update values

$tenant = app()->tenant;

if (isset($_POST['facebook'])) {
  $tenant->setKey('FACEBOOK_PAGE', trim((string) $_POST['facebook']));
} else {
  $tenant->setKey('FACEBOOK_PAGE', null);
}

if (isset($_POST['twitter'])) {
  $tenant->setKey('TWITTER_ACCOUNT', trim((string) $_POST['twitter']));
} else {
  $tenant->setKey('TWITTER_ACCOUNT', null);
}

if (isset($_POST['noticeboard'])) {
  $tenant->setSwimEnglandComplianceValue('NOTICEBOARD_LOCATIONS', trim((string) $_POST['noticeboard']));
} else {
  $tenant->setSwimEnglandComplianceValue('NOTICEBOARD_LOCATIONS', null);
}

if (isset($_POST['where-to-find-updates'])) {
  $tenant->setSwimEnglandComplianceValue('NEWS_LOCATIONS', trim((string) $_POST['where-to-find-updates']));
} else {
  $tenant->setSwimEnglandComplianceValue('NEWS_LOCATIONS', null);
}

if (isset($_POST['welfare-name'])) {
  $tenant->setSwimEnglandComplianceValue('WELFARE_NAME', trim((string) $_POST['welfare-name']));
} else {
  $tenant->setSwimEnglandComplianceValue('WELFARE_NAME', null);
}

if (isset($_POST['welfare-email'])) {
  $tenant->setSwimEnglandComplianceValue('WELFARE_EMAIL', trim((string) $_POST['welfare-email']));
} else {
  $tenant->setSwimEnglandComplianceValue('WELFARE_EMAIL', null);
}

if (isset($_POST['welfare-phone'])) {
  $tenant->setSwimEnglandComplianceValue('WELFARE_PHONE', trim((string) $_POST['welfare-phone']));
} else {
  $tenant->setSwimEnglandComplianceValue('WELFARE_PHONE', null);
}

if (isset($_POST['complaints-process'])) {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_PROCESS', trim((string) $_POST['complaints-process']));
} else {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_PROCESS', null);
}

if (isset($_POST['complaints-name'])) {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_OFFICER', trim((string) $_POST['complaints-name']));
} else {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_OFFICER', null);
}

if (isset($_POST['complaints-email'])) {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_EMAIL', trim((string) $_POST['complaints-email']));
} else {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_EMAIL', null);
}

if (isset($_POST['complaints-phone'])) {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_PHONE', trim((string) $_POST['complaints-phone']));
} else {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_PHONE', null);
}

if (isset($_POST['facility-info'])) {
  $tenant->setSwimEnglandComplianceValue('FACILITY_INFORMATION', trim((string) $_POST['facility-info']));
} else {
  $tenant->setSwimEnglandComplianceValue('FACILITY_INFORMATION', null);
}

if (isset($_POST['swimmark'])) {
  $tenant->setSwimEnglandComplianceValue('SWIMMARK_STATUS', trim((string) $_POST['swimmark']));
} else {
  $tenant->setSwimEnglandComplianceValue('SWIMMARK_STATUS', null);
}

if (isset($_POST['swimmark-start'])) {
  $tenant->setSwimEnglandComplianceValue('SWIMMARK_START', trim((string) $_POST['swimmark-start']));
} else {
  $tenant->setSwimEnglandComplianceValue('SWIMMARK_START', null);
}

if (isset($_POST['swimmark-end'])) {
  $tenant->setSwimEnglandComplianceValue('SWIMMARK_END', trim((string) $_POST['swimmark-end']));
} else {
  $tenant->setSwimEnglandComplianceValue('SWIMMARK_END', null);
}

if (isset($_POST['volunteering-opportunities'])) {
  $tenant->setSwimEnglandComplianceValue('VOLUNTEERING_INFORMATION', trim((string) $_POST['volunteering-opportunities']));
} else {
  $tenant->setSwimEnglandComplianceValue('VOLUNTEERING_INFORMATION', null);
}

if (isset($_POST['youth-engagement'])) {
  $tenant->setSwimEnglandComplianceValue('YOUTH_ENGAGEMENT_INFORMATION', trim((string) $_POST['youth-engagement']));
} else {
  $tenant->setSwimEnglandComplianceValue('YOUTH_ENGAGEMENT_INFORMATION', null);
}

header("location: " . autoUrl("admin/swim-england-compliance"));
