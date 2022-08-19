<?php

// Update values

$tenant = app()->tenant;

if (isset($_POST['facebook'])) {
  $tenant->setKey('FACEBOOK_PAGE', trim($_POST['facebook']));
} else {
  $tenant->setKey('FACEBOOK_PAGE', null);
}

if (isset($_POST['twitter'])) {
  $tenant->setKey('TWITTER_ACCOUNT', trim($_POST['twitter']));
} else {
  $tenant->setKey('TWITTER_ACCOUNT', null);
}

if (isset($_POST['noticeboard'])) {
  $tenant->setSwimEnglandComplianceValue('NOTICEBOARD_LOCATIONS', trim($_POST['noticeboard']));
} else {
  $tenant->setSwimEnglandComplianceValue('NOTICEBOARD_LOCATIONS', null);
}

if (isset($_POST['where-to-find-updates'])) {
  $tenant->setSwimEnglandComplianceValue('NEWS_LOCATIONS', trim($_POST['where-to-find-updates']));
} else {
  $tenant->setSwimEnglandComplianceValue('NEWS_LOCATIONS', null);
}

if (isset($_POST['welfare-name'])) {
  $tenant->setSwimEnglandComplianceValue('WELFARE_NAME', trim($_POST['welfare-name']));
} else {
  $tenant->setSwimEnglandComplianceValue('WELFARE_NAME', null);
}

if (isset($_POST['welfare-email'])) {
  $tenant->setSwimEnglandComplianceValue('WELFARE_EMAIL', trim($_POST['welfare-email']));
} else {
  $tenant->setSwimEnglandComplianceValue('WELFARE_EMAIL', null);
}

if (isset($_POST['welfare-phone'])) {
  $tenant->setSwimEnglandComplianceValue('WELFARE_PHONE', trim($_POST['welfare-phone']));
} else {
  $tenant->setSwimEnglandComplianceValue('WELFARE_PHONE', null);
}

if (isset($_POST['complaints-process'])) {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_PROCESS', trim($_POST['complaints-process']));
} else {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_PROCESS', null);
}

if (isset($_POST['complaints-name'])) {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_OFFICER', trim($_POST['complaints-name']));
} else {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_OFFICER', null);
}

if (isset($_POST['complaints-email'])) {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_EMAIL', trim($_POST['complaints-email']));
} else {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_EMAIL', null);
}

if (isset($_POST['complaints-phone'])) {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_PHONE', trim($_POST['complaints-phone']));
} else {
  $tenant->setSwimEnglandComplianceValue('COMPLAINTS_PHONE', null);
}

if (isset($_POST['facility-info'])) {
  $tenant->setSwimEnglandComplianceValue('FACILITY_INFORMATION', trim($_POST['facility-info']));
} else {
  $tenant->setSwimEnglandComplianceValue('FACILITY_INFORMATION', null);
}

if (isset($_POST['swimmark'])) {
  $tenant->setSwimEnglandComplianceValue('SWIMMARK_STATUS', trim($_POST['swimmark']));
} else {
  $tenant->setSwimEnglandComplianceValue('SWIMMARK_STATUS', null);
}

if (isset($_POST['swimmark-start'])) {
  $tenant->setSwimEnglandComplianceValue('SWIMMARK_START', trim($_POST['swimmark-start']));
} else {
  $tenant->setSwimEnglandComplianceValue('SWIMMARK_START', null);
}

if (isset($_POST['swimmark-end'])) {
  $tenant->setSwimEnglandComplianceValue('SWIMMARK_END', trim($_POST['swimmark-end']));
} else {
  $tenant->setSwimEnglandComplianceValue('SWIMMARK_END', null);
}

if (isset($_POST['volunteering-opportunities'])) {
  $tenant->setSwimEnglandComplianceValue('VOLUNTEERING_INFORMATION', trim($_POST['volunteering-opportunities']));
} else {
  $tenant->setSwimEnglandComplianceValue('VOLUNTEERING_INFORMATION', null);
}

if (isset($_POST['youth-engagement'])) {
  $tenant->setSwimEnglandComplianceValue('YOUTH_ENGAGEMENT_INFORMATION', trim($_POST['youth-engagement']));
} else {
  $tenant->setSwimEnglandComplianceValue('YOUTH_ENGAGEMENT_INFORMATION', null);
}

header("location: " . autoUrl("admin/swim-england-compliance"));
