<?php
$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];

$this->get('/all-galas', function () {
	include 'list.php';
});

// Gala Entry Time Sheet
$this->get(['/{id}:int/timesheet', '/competitions/{id}:int/timesheet'], function ($id) {

	include "export/PDFTimesheet.php";
});

$this->get(['/{id}:int/timesheet.csv', '/competitions/{id}:int/timesheet.csv'], function ($id) {
	include "export/TimeSheet.php";
});

/**
 * SQUAD REP URLS
 */
$this->get('/{id}:int/squad-rep-view', function ($id) {
	include 'squad-reps-and-team-managers/info.php';
});

$this->get('/{id}:int/squad-rep-view.json', function ($id) {
	include 'squad-reps-and-team-managers/infoOutput.json.php';
});

$this->get('/{id}:int/squad-rep-view.csv', function ($id) {
	include 'squad-reps-and-team-managers/info.csv.php';
});

$this->get('/{id}:int/squad-rep-view.pdf', function ($id) {
	include 'squad-reps-and-team-managers/info.pdf.php';
});

$this->post('/squad-reps/entry-states', function () {
	include 'squad-reps-and-team-managers/handle-entry-state.php';
});

/**
 * TEAM MANAGER URLS
 */

$this->get('/{id}:int/team-manager', function ($id) {
	include 'squad-reps-and-team-managers/team-manager-gala-page.php';
});

$this->get('/{id}:int/team-manager-view', function ($id) {
	include 'squad-reps-and-team-managers/tm.php';
});

$this->get('/{id}:int/team-manager-view.json', function ($id) {
	include 'squad-reps-and-team-managers/tmOutput.json.php';
});

$this->get('/{id}:int/team-manager-view.csv', function ($id) {
	include 'squad-reps-and-team-managers/tm.csv.php';
});

$this->get('/{id}:int/team-manager-view.pdf', function ($id) {
	include 'squad-reps-and-team-managers/tm.pdf.php';
});

$this->get('/{id}:int/swimmers', function ($id) {
	include 'squad-reps-and-team-managers/tm-swimmer-info.php';
});

$this->get('/{id}:int/photography-permissions.pdf', function ($id) {
	include 'export/PhotoPermissions.php';
});

$this->get('/{id}:int/register', function ($id) {
	include 'attendance/PaperRegister.php';
});

$this->get('/ajax/entryForm', function () {
	include BASE_PATH . "controllers/ajax/galaForm.php";
});

$this->get('/entergala/help', function () {
	include 'help/entry.php';
});

$this->get('/{id}:int/finance-info.csv', function ($id) {
	include 'export/gala-financial-report.php';
});


if ($access == "Parent") {
	// Gala Home
	$this->get('/', function () {
		// Check docs for route - this is a GET

		include 'parentHome.php';
	});

	// View a gala
	$this->get('/{id}:int', function ($id) {
		include 'Gala.php';
	});

	// Enter a gala
	$this->group(['/entergala'], function () {
		if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['SuccessfulGalaEntry'])) {
			$this->get('/', function () {
				include 'GalaEntrySuccess.php';
			});
		} else {
			$this->get('/', function () {
				include 'GalaEntryForm.php';
			});
		}

		$this->post('/', function () {
			include 'GalaEntryFormPost.php';
		});
	});

	// Gala Entries
	$this->get('/entries', function () {

		include 'parententries.php';
	});

	$this->get('/entries/{id}', function ($id) {

		include 'EditEntry.php';
	});

	$this->post('/entries/{id}', function ($id) {

		include 'EditEntryPost.php';
	});

	$this->get('/entries/{id}:int/manual-time', function ($id) {
		include 'AddManualTime.php';
	});

	$this->post('/entries/{id}:int/manual-time', function ($id) {
		include 'AddManualTimePost.php';
	});

	$this->get('/entries/{id}/veto', function ($id) {
		include 'indicate-openness/veto.php';
	});

	$this->get('/entries/{id}/veto/do', function ($id) {

		include 'indicate-openness/veto-do.php';
	});

	if (getenv('STRIPE') && app()->tenant->getStripeAccount() && app()->tenant->getBooleanKey('GALA_CARD_PAYMENTS_ALLOWED')) {
		$this->group('/pay-for-entries', function () {
			$this->get('/', function () {
				include 'pay/welcome.php';
			});

			$this->post('/', function () {
				include 'pay/welcome-post.php';
			});

			$this->post('/checkout-v1', function () {
				include 'pay/welcome-post-checkout-v1.php';
			});

			$this->post('/switch-method', function () {
				include 'pay/switch-method.php';
			});

			$this->get('/checkout', function () {
				include 'pay/checkout-page.php';
			});

			$this->get('/checkout/new', function () {
				include 'pay/checkout-page.php';
			});

			$this->get('/complete', function () {
				$newMethod = false;
				include 'pay/complete.php';
			});

			$this->get('/complete/new', function () {
				$newMethod = true;
				include 'pay/complete.php';
			});

			$this->get('/success', function () {
				include 'pay/success.php';
			});
		});
	}
} else if ($access == "Galas" || $access == "Committee" || $access == "Admin" || $access == "Coach") {
	// Gala Home
	$this->get(['/', '/competitions'], function () {

		include 'listGalas.php';
	});

	// Add a gala
	$this->get(['/addgala', '/new'], function () {

		include 'addGala.php';
	});

	$this->post(['/addgala', '/new'], function () {

		include 'addGalaAction.php';
	});


	$this->get('/{id}:int', function ($id) {
		include 'Gala.php';
	});

	$this->get('/{id}:int/export.pdf', function ($id) {
		include 'gala.pdf.php';
	});

	// Manage pricing and events
	$this->get(['/{id}:int/pricing-and-events'], function ($id) {
		include "pricing-and-events/edit.php";
	});

	$this->post(['/{id}:int/pricing-and-events'], function ($id) {
		include "pricing-and-events/edit-post.php";
	});

	// View Competitions
	$this->get(['/{id}:int/edit', '/competitions/{id}:int/edit'], function ($id) {
		include "competitionSingle.php";
	});

	$this->post(['/{id}:int/edit', '/competitions/{id}:int/edit'], function ($id) {
		include "CompetitionSinglePost.php";
	});

	// Gala Entries
	$this->get('/entries/{id}:int', function ($id) {

		include 'EditEntry.php';
	});

	$this->post('/entries/{id}:int', function ($id) {

		include 'EditEntryPost.php';
	});

	// Gala Entries
	$this->get('/entries', function () {

		include 'allEntries.php';
	});

	$this->get('/ajax/entries', function () {

		require BASE_PATH . 'controllers/ajax/GalaEntries.php';
	});

	$this->post('/ajax/entryProcessed', function () {

		include BASE_PATH . 'controllers/ajax/galaEntriesProcessed.php';
	});

	$this->get('/entries/{id}:int', function ($id) {

		include 'singleentry.php';
	});

	$this->get('/entries/{id}:int/manual-time', function ($id) {
		include 'AddManualTime.php';
	});

	$this->post('/entries/{id}:int/manual-time', function ($id) {
		include 'AddManualTimePost.php';
	});

	$this->get('/{id}:int/select-entries', function ($id) {
		include 'indicate-openness/select-swims.php';
	});

	$this->post('/{id}:int/select-entries', function ($id) {
		include 'indicate-openness/select-swims-post.php';
	});

	$this->get('/{id}:int/invite-parents', function ($id) {
		include 'indicate-openness/invite-parents.php';
	});

	$this->post('/{id}:int/invite-parents', function ($id) {
		include 'indicate-openness/invite-parents-post.php';
	});
}

if ($access == "Galas" || $access == "Admin") {

	$this->get('/charges-and-refunds', function () {
		include BASE_PATH . 'controllers/payments/galas/Home.php';
	});

	$this->get('/{id}:int/charges', function ($id) {
		include BASE_PATH . 'controllers/payments/galas/EntryCharge.php';
	});

	$this->post('/{id}:int/charges', function ($id) {
		include BASE_PATH . 'controllers/payments/galas/EntryChargeAction.php';
	});

	$this->get('/{id}:int/refunds', function ($id) {
		include BASE_PATH . 'controllers/payments/galas/RefundCharge.php';
	});

	$this->post('/payments/ajax-refund-handler', function () {
		include BASE_PATH . 'controllers/payments/galas/RefundChargeAjax.php';
	});


	$this->get('/{id}:int/sessions', function ($id) {
		include 'indicate-openness/gala-sessions.php';
	});

	$this->get('/{id}:int/sessions/{session}:int/delete', function ($id, $session) {
		include 'indicate-openness/delete-session.php';
	});

	$this->post('/{id}:int/sessions', function ($id) {
		include 'indicate-openness/gala-sessions-post.php';
	});
}

if ($access == "Galas" || $access == "Admin" || $access == "Coach") {
	$this->get('/{id}:int/create-registers', function ($id) {
		include 'create-registers.php';
	});

	$this->post('/{id}:int/create-registers', function ($id) {
		include 'create-registers-post.php';
	});
}

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
	$this->get('/{id}:int/indicate-availability', function ($id) {
		include 'indicate-openness/session-select.php';
	});

	$this->post('/{id}:int/indicate-availability', function ($id) {
		include 'indicate-openness/session-select-post.php';
	});
}

$this->get('/{id}:int/registers', function ($id) {
	include 'attendance/Register.php';
});

$this->post('/{id}:int/registers', function ($id) {
	include 'attendance/RegisterPost.php';
});
