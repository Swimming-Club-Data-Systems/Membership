<?php

$this->any('/sumpayments', function () {
	require 'sumpayments.php';
});

$this->any('/chargeusers', function () {
	try {
		if (app()->tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT')) {
			include 'charge-users-stripe.php';
		} else {
			include 'charge-users-gc-legacy.php';
		}
	} catch (Exception $e) {
		reportError($e);
	}
});

$this->any('/retrypayments', function () {
	require 'retry-payments.php';
});

$this->any('/notifysend', function () {

	$db = app()->db;
	//echo "Service Suspended";
	require 'SingleEmailHandler.php';
});

$this->any('/newnotifysend', function () {
	require 'notifyhandler.php';
});

$this->any('/handle-legacy-renewal-period-creation', function () {
	$db = app()->db;;
	require 'squadmemberupdate.php';
});

$this->any('/updateregisterweeks', function () {
	$db = app()->db;;
	require 'newWeek.php';
});

$this->any('/timeupdate', function () {
	$db = app()->db;;
	require 'getTimesNew.php';
});

$this->post('/checkout_v1', function () {
	require 'checkout_v1.php';
});

/*$this->any('/timeupdatenew', function() {
	$db = app()->db;;
	require 'getTimesNew.php';
});*/
