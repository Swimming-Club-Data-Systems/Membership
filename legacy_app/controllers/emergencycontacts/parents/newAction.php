<?php

$db = DB::connection()->getPdo();

$url_path = "emergency-contacts";
if ($renewal_trap) {
	$url_path = "renewal/emergencycontacts";
}

$user = $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'];

$contact = new EmergencyContact();
$contact->connect($db);

if ($_POST['name'] != null && $_POST['name'] != "" && $_POST['num'] != null && $_POST['num'] != "") {
	try {
		if (isset($_POST['relation']) && $_POST['relation'] != "") {
			$contact->new($_POST['name'], $_POST['num'], $user, $_POST['relation']);
		} else {
			$contact->new($_POST['name'], $_POST['num'], $user);
		}
		$contact->add();

		$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['AddNewSuccess'] = '
		<div class="alert alert-success">
			<p class="mb-0">
				<strong>
					Emergency Contact added successfully
				</strong>
			</p>
		</div>
		';

		if ($renewal_trap) {
			header("Location: " . autoUrl("renewal/go"));
		} else {
			header("Location: " . autoUrl($url_path));
		}
	} catch (Exception $e) {
		$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['AddNewError'] = '
		<div class="alert alert-warning">
			<p class="mb-0">
				<strong>
					There was a problem with some of the data you supplied
				</strong>
			</p>
			<p class="mb-0">Your phone number might not be valid</p>
		</div>
		';
		$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['POST_DATA'] = $_POST;
		header("Location: " . autoUrl($url_path . "/new"));
	}

} else {
	$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['AddNewError'] = '
	<div class="alert alert-warning">
		<p class="mb-0">
			<strong>
				We were unable to add the contact
			</strong>
		</p>
	</div>
	';

	if ($renewal_trap) {
		header("Location: " . autoUrl("renewal/go"));
	} else {
		header("Location: " . autoUrl($url_path));
	}

}
