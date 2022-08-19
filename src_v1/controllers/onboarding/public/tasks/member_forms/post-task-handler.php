<?php

// Load a task page

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

$member = \SCDS\Onboarding\Member::retrieveById($id);

$currentTask = $member->getCurrentTask();

include 'tasks/' . escapeshellcmd($currentTask) . '/post.php';