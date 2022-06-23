<?php

$this->group('/mandates', function () {
	if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
		$this->get('/', function () {
			include 'mybanks.php';
		});

		$this->get('/{id}:int/set-default', function ($id) {
			include 'setup/makedefault.php';
		});
	}

	if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent" || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin") {
		$this->get('/{mandate}/print', function ($mandate) {
			include 'mandatePDFs.php';
		});
	}

	$this->get('/{mandate}/', function ($mandate) {
		if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') {
			include 'admin/user-mandates/mandate-info.php';
		} else {
			header("location: " . autoUrl("payments/mandates/" . $mandate . "/print"));
		}
	});

	if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin") {
		$this->get('/', function () {
			include 'admin/user-mandates/mandates.php';
		});

		$this->get('/{mandate}/cancel', function ($mandate) {
			include 'admin/user-mandates/mandate-cancel.php';
		});

		// API TOOL FOR FUTURE?
		$this->delete('/{mandate}', function ($mandate) {
			include 'admin/user-mandates/mandate-cancel.php';
		});
	}
});

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') {
	$this->group('/categories', function () {
		include 'categories/router.php';
	});
}

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {

	$this->get('/', function () {
		include 'user.php';
	});

	$this->get(['setup', '/setup/{stage}:int'], function ($stage = 0) {

		require 'GoCardlessSetup.php';
		if ($stage == 0) {
			require('setup/start.php');
		} else if ($stage == 1) {
			require('setup/date.php');
		} else if ($stage == 2) {
			require('setup/initiate.php');
		} else if ($stage == 3) {
			require('setup/redirect.php');
		} else if ($stage == 4) {
			require('setup/status.php');
		}
	});

	$this->post('/setup/1', function () {

		include 'setup/datepost.php';
	});

	$this->get('/squad-fees', function () {
		include 'parent/SquadFees.php';
	});

	$this->get('/membership-fees', function () {
		include 'parent/MembershipFees.php';
	});

	$this->get(['/currentfees', '/fees'], function () {

		include 'parent/currentfees.php';
	});

	$this->get('/transactions', function () {
		include 'parent/transactions.php';
	});
}

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin") {
	$this->get('/', function () {

		include 'admin.php';
	});

	$this->get('/current', function () {

		include 'users/ListOfUsers.php';
	});

	$this->post('/current/ajax', function () {

		include 'users/ListOfUsersAjaxBackend.php';
	});

	$this->get('/current/{id}', function ($id) {

		include 'users/current.php';
	});

	$this->get('/fees', function () {

		include 'adminViewMonthlyFees.php';
	});

	$this->get('/estimated-fees', function () {

		include 'admin/reports/trial-run.php';
	});

	$this->group('/confirmation', function () {
		include 'admin/confirmation/router.php';
	});

	$this->group('/invoice-payments', function () {
		require 'admin/invoicing/router.php';
	});

	$this->get('/user-mandates/go-cardless', function () {
		include 'admin/user-mandates/UserMandateList.php';
	});

	$this->get('/user-mandates', function () {
		include 'admin/user-mandates/StripeUserMandateList.php';
	});

	$this->group('/history', function () {


		$this->get('/', function () {

			include 'admin/history/home.php';
		});

		$this->get('/users', function () {

			include 'admin/history/UserList.php';
		});

		$this->get('/users/{id}:int', function ($id) {

			include 'admin/history/users.php';
		});

		$this->get('/{year}:int/{month}:int', function ($year, $month) {

			include 'admin/history/month.php';
		});

		$this->get('/{year}:int/{month}:int/report.csv', function ($year, $month) {
			include 'admin/history/FinanceReport.csv.php';
		});

		$this->get('/{year}:int/{month}:int/report.json', function ($year, $month) {
			include 'admin/history/FinanceReportOutput.json.php';
		});

		$this->get('/{year}:int/{month}:int/report.pdf', function ($year, $month) {
			include 'admin/history/FinanceReport.pdf.php';
		});

		$this->get('/{year}:int/{month}:int/payment-item-report.csv', function ($year, $month) {
			include 'admin/history/PaymentItemReport.csv.php';
		});

		$this->get('/{year}:int/{month}:int/payment-item-report.json', function ($year, $month) {
			include 'admin/history/PaymentItemReportOutput.json.php';
		});

		$this->get('/{type}/{year}:int/{month}:int', function ($type, $year, $month) {

			include 'admin/history/feestatus.php';
		});

		$this->get('/{type}/{year}:int/{month}:int/csv', function ($type, $year, $month) {
			include BASE_PATH . 'controllers/squads/CSVSquadExport.php';
		});

		$this->get('/{type}/{year}:int/{month}:int/json', function ($type, $year, $month) {
			include 'admin/history/JSONSquadExport.php';
		});
	});

	$this->group('/extrafees', function () {


		$this->get('/', function () {

			include 'admin/ExtrasList.php';
		});

		$this->get('/new', function () {

			include 'admin/NewExtra.php';
		});

		$this->post('/new', function () {

			include 'admin/NewExtraServer.php';
		});

		$this->get('/{id}', function ($id) {

			include 'admin/ExtraIndividual.php';
		});

		$this->get('/{id}/edit', function ($id) {

			include 'admin/EditExtra.php';
		});

		$this->post('/{id}/edit', function ($id) {

			include 'admin/EditExtraServer.php';
		});

		$this->get('/{id}/delete', function ($id) {

			include 'admin/ExtraDelete.php';
		});

		$this->post('ajax/{id}:int', function ($id) {

			include 'admin/ExtraIndividualServer.php';
		});
	});

	$this->get('/galas', function () {

		include 'galas/Home.php';
	});
}

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Galas") {
	$this->get(['/', '/galas'], function () {

		include 'galas/Home.php';
	});
}

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Galas" || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin") {
	$this->get('/galas/{id}:int', function ($id) {

		include 'galas/EntryCharge.php';
	});

	$this->post('/galas/{id}:int', function ($id) {

		include 'galas/EntryChargeAction.php';
	});

	$this->get('/galas/{id}:int/refund', function ($id) {

		include 'galas/RefundCharge.php';
	});
}

// Only allow payment cards if not null
if (getenv('STRIPE') && app()->tenant->getStripeAccount()) {

	/*
	* Payment Cards
	* Available to all users
	*/

	$this->group('/cards', function () {
		$this->get('/', function () {
			include 'stripe/home.php';
		});

		$this->group('/pay', function () {
			$this->get('/', function () {
				include 'stripe/checkout/charges.php';
			});

			$this->get('/charge', function () {
				include 'stripe/checkout/init-payment.php';
			});

			$this->get('/charge', function () {
				include 'stripe/checkout/init-payment.php';
			});

			$this->get('/custom-amount', function () {
				include 'stripe/terminal/pay-custom-amount.php';
			});
		});

		$this->get('/add', function () {
			include 'stripe/AddPaymentMethod.php';
		});

		$this->post('/add', function () {
			include 'stripe/AddPaymentMethodPost.php';
		});

		$this->get('/{id}:int', function ($id) {
			include 'stripe/EditPaymentMethod.php';
		});

		$this->get('/{id}:int/delete', function ($id) {
			include 'stripe/DeleteCard.php';
		});

		if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Parent") {
			$this->group('/terminal', function () {
				include 'stripe/terminal/router.php';
			});
		}
	});

	if (app()->tenant->getBooleanKey('ALLOW_STRIPE_DIRECT_DEBIT_SET_UP') || app()->tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT')) {
		$this->group('/direct-debit', function () {
			$this->get('/', function () {
				include 'stripe/direct-debit/info.php';
			});

			$this->get('/mandate/{id}/view-ddi', function ($id) {
				include 'stripe/direct-debit/get-ddi.php';
			});

			$this->group('/set-up', function () {
				$this->get('/', function () {
					include 'stripe/direct-debit/sign-up.php';
				});

				$this->get('/success', function () {
					include 'stripe/direct-debit/success.php';
				});

				$this->get('/cancel', function () {
					include 'stripe/direct-debit/failure.php';
				});
			});
		});
	}
}

$this->group('/card-transactions', function () {
	$this->get(['/', '/page/{page}:int'], function ($page = null) {
		include 'stripe/history/history.php';
	});

	$this->get('/{id}:int', function ($id) {
		include 'stripe/history/payment.php';
	});

	$this->post('/refund', function () {
		include 'stripe/history/payment-refund.php';
	});

	$this->get('/{id}:int/receipt.pdf', function ($id) {
		include 'stripe/history/payment.pdf.php';
	});
});

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') {
	$this->group('/statements', function () {
		if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
			$this->get('/', function () {
				include 'parent/transactions.php';
			});

			$this->get('/latest', function () {
				include 'parent/LatestStatement.php';
			});
		}

		$this->get('/{id}:int', function ($id) {
			include 'admin/history/statement.php';
		});

		$this->get('/{id}:int/pdf', function ($id) {
			include 'admin/history/statementPDF.php';
		});

		$this->get('/{id}:int/mark-paid/{token}', function ($id, $token) {

			include 'admin/history/StatementMarkPaid.php';
		});
	});
}

$this->group('/disputes', function() {
	include 'stripe/disputes/router.php';
});