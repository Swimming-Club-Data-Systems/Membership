<?php

$this->any('/sumpayments', function () {
	require 'sumpayments.php';
});

$this->any('/chargeusers', function () {
	try {
		if (config('USE_STRIPE_DIRECT_DEBIT')) {
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

	$db = DB::connection()->getPdo();
	//echo "Service Suspended";
	require 'SingleEmailHandler.php';
});

$this->any('/newnotifysend', function () {
	require 'notifyhandler.php';
});

$this->any('/handle-legacy-renewal-period-creation', function () {
	$db = DB::connection()->getPdo();
	require 'squadmemberupdate.php';
});

$this->any('/updateregisterweeks', function () {
	$db = DB::connection()->getPdo();
	require 'newWeek.php';
});

$this->any('/timeupdate', function () {
	$db = DB::connection()->getPdo();
	require 'getTimesNew.php';
});

$this->post('/checkout_v1', function () {
	require 'checkout_v1.php';
});

$this->post('/checkout_v2', function () {
	require 'checkout_v2.php';
});

/*$this->any('/timeupdatenew', function() {
	$db = DB::connection()->getPdo();;
	require 'getTimesNew.php';
});*/
