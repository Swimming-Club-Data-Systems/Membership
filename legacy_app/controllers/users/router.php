<?php
$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserSimulation'])) {
	$this->get('/simulate/exit', function () {

		include 'ExitSimulation.php';
	});
}

if ($access == "Committee" || $access == "Admin" || $access == "Galas") {
	// User Directory
	$this->get(['/', '/filter'], function ($id = null) {

		include 'userDirectory.php';
	});

	$this->any('/ajax/userList', function () {

		include BASE_PATH . "controllers/ajax/userList.php";
	});

	$this->any('/ajax/resend-registration-email', function () {
		include 'ResendRegEmail.php';
	});

	$this->get('/{id}:int', function ($id) {
		include 'user.php';
	});

	$this->get('/{id}:int/current-memberships', function ($id) {
		include 'current-memberships.php';
	});

	$this->get('/{id}:int/new-membership-batch', function ($id) {
		include 'new-membership-batch.php';
	});

	$this->get('/{id}:int/new-membership-batch-select', function ($id) {
		include 'new-membership-batch-select.php';
	});

	if ($access == "Admin") {
		$this->group('/{id}:int/edit', function ($id) {
			$this->get('/', function ($id) {
				include 'Edit.php';
			});

			$this->post('/', function ($id) {
				include 'EditPost.php';
			});

			$this->post('/email', function ($id) {
				include 'EditEmailAjax.php';
			});
		});

		$this->get('/{user}:int/mandates', function ($user) {
			include BASE_PATH . 'controllers/payments/admin/user-mandates/user-mandates.php';
		});

		$this->group('/{user}:int/direct-debit', function ($user) {
			$this->get('/', function ($user) {
				include BASE_PATH . 'controllers/payments/admin/user-mandates/stripe-user-mandates.php';
			});

			$this->post('/force-run-info', function ($id) {
				include 'direct-debit/trigger-direct-debit-payment.php';
			});

			$this->post('/force-run-submission', function ($id) {
				include 'direct-debit/trigger-direct-debit-payment-post.php';
			});
		});
	}

	$this->get('/{id}:int/welcome-pack', function ($id) {
		include BASE_PATH . 'controllers/registration/welcome-pack/PDF.php';
	});

	$this->get('/{id}:int/welcome-letter', function ($id) {
		include BASE_PATH . 'controllers/registration/welcome-pack/letter.php';
	});

	$this->get('/{id}:int/authorise-direct-debit-opt-out', function ($person) {
		include 'direct-debit/OptOutInfo.php';
	});

	$this->post('/{id}:int/authorise-direct-debit-opt-out', function ($person) {
		include 'direct-debit/OptOutPost.php';
	});

	$this->get('/{id}:int/qualifications', function ($person) {
		include BASE_PATH . 'controllers/qualifications/MyQualifications.php';
	});

	$this->get('/{id}:int/qualifications/new', function ($person) {
		include BASE_PATH . 'controllers/qualifications/admin/NewQualification.php';
	});

	$this->post('/{id}:int/qualifications/new', function ($person) {
		include BASE_PATH . 'controllers/qualifications/admin/NewQualificationPost.php';
	});

	$this->get('/{person}:int/qualifications/{id}:int', function ($person, $id) {
		include BASE_PATH . 'controllers/qualifications/admin/NewQualification.php';
	});

	$this->post('/{person}:int/qualifications/{id}:int', function ($person, $id) {
		include BASE_PATH . 'controllers/qualifications/admin/NewQualification.php';
	});

	$this->get('/{id}:int/membership-fees', function ($id) {
		include BASE_PATH . 'controllers/payments/parent/MembershipFees.php';
	});

	if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserSimulation'])) {
		$this->get('/simulate/{id}:int', function ($id) {

			include 'EnterSimulation.php';
		});
	}

	$this->post('/ajax/username', function () {
		include 'usernameAjax.php';
	});
}

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') {

	/**
	 * DELETION
	 */

	$this->post('/delete-user', function () {
		include 'delete.php';
	});

	/**
	 * EMAIL ADDRESS UPDATES
	 */

	$this->post('/{id}:int/update-email-address', function ($id) {
		include 'EditEmailPost.php';
	});

	/**
	 * Set/revoke squad (AJAX)
	 */
	$this->get('/assign-revoke-squad', function ($id) {
		include 'coaches/assign-revoke-squad-post.php';
	});

	/**
	 * PAGES FOR SETTING SQUAD REPS
	 */
	$this->get('/{id}:int/rep', function ($id) {
		include 'squad-reps/list.php';
	});

	$this->get('/{id}:int/rep/add', function ($id) {
		include 'squad-reps/add.php';
	});

	$this->post('/{id}:int/rep/add', function ($id) {
		include 'squad-reps/add-post.php';
	});

	$this->get('/{id}:int/rep/remove', function ($id) {
		include 'squad-reps/remove.php';
	});

	/**
	 * PAGES FOR SETTING GALA TEAM MANAGERS
	 */
	$this->get('/{id}:int/team-manager', function ($id) {
		include 'team-managers/list.php';
	});

	$this->get('/{id}:int/team-manager/add', function ($id) {
		include 'team-managers/add.php';
	});

	$this->post('/{id}:int/team-manager/add', function ($id) {
		include 'team-managers/add-post.php';
	});

	$this->get('/{id}:int/team-manager/remove', function ($id) {
		include 'team-managers/remove.php';
	});

	/**
	 * PAGES FOR SETTING ACCESS TO TARGETED LISTS
	 */
	$this->get('/{id}:int/targeted-lists', function ($id) {
		include 'notify-lists/list.php';
	});

	$this->get('/{id}:int/targeted-lists/add', function ($id) {
		include 'notify-lists/add.php';
	});

	$this->post('/{id}:int/targeted-lists/add', function ($id) {
		include 'notify-lists/add-post.php';
	});

	$this->get('/{id}:int/targeted-lists/remove', function ($id) {
		include 'notify-lists/remove.php';
	});

	/**
	 * FINANCIAL INFORMATION
	 */

	$this->get('/{id}:int/pending-fees', function ($id) {
		include 'CurrentFees.php';
	});

	/**
	 * CONTACT BY EMAIL
	 */

	$this->group('/{user}:int/email', function ($user) {
		$this->get('/', function ($user) {
			$userOnly = true;
			include BASE_PATH . 'controllers/notify/EmailIndividual.php';
		});

		$this->post('/', function ($user) {
			$userOnly = true;
			include BASE_PATH . 'controllers/notify/EmailQueuerIndividual.php';
		});
	});
}

/**
 * SQUAD COACHES
 */

$this->group('/squads', function () {
	$this->post('/list', function () {
		include 'coaches/squad-list.php';
	});

	$this->post('/assign-delete', function () {
		include 'coaches/assign-revoke-squad-post.php';
	});
});

if (app()->user->hasPermission('Admin')) {
	$this->group('/add', function () {
		include 'new/router.php';
	});
}