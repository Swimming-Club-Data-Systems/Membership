<?php

// Load a task page

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

$currentTask = $session->getCurrentTask();

include 'tasks/' . escapeshellcmd($currentTask) . '/start.php';