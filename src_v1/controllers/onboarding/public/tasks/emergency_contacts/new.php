<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$db = app()->db;

$contact = new EmergencyContact();
$contact->connect($db);

$good = true;

if (isset($_POST['name']) && $_POST['name'] != "" && isset($_POST['num']) && $_POST['num'] != "") {
  try {
    if (isset($_POST['relation']) && $_POST['relation'] != "") {
      $contact->new($_POST['name'], $_POST['num'], $user->getId(), $_POST['relation']);
    } else {
      $contact->new($_POST['name'], $_POST['num'], $user->getId());
    }
    $contact->add();

    $_SESSION['TENANT-' . app()->tenant->getId()]['AddNewSuccess'] = '
		<div class="alert alert-success">
			<p class="mb-0">
				<strong>
					Emergency Contact added successfully
				</strong>
			</p>
		</div>
		';
  } catch (Exception $e) {
    $good = false;
  }
} else {

  $good = false;
}

header("content-type: application/json");

echo json_encode([
  'success' => $good,
]);
