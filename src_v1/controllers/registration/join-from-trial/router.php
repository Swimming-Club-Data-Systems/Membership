<?php

/*
$this->get('/', function() {
  include 'AboutAC.php';
});
*/

/**
 * Begin user data collection
 */

 /*
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Stage'] == 'UserDetails') {
  $this->get('/user-details', function() {
    include 'CollectUserDetails.php';
  });

  $this->post('/user-details', function() {
    include 'CollectUserDetailsPost.php';
  });
}

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Stage'] == 'VerifyEmail') {
  $this->get('/verify-email', function() {
    include 'VerifyUserEmail.php';
  });

  $this->post('/verify-email', function() {
    include 'VerifyUserEmailPost.php';
  });

  $this->post('/verify-email/modify', function() {
    include 'VerifyUserEmailModify.php';
  });

  $this->get('/verify-email/resend', function() {
    include 'VerifyUserEmailResend.php';
  });
}

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Stage'] == 'TermsConditions') {
  $this->get('/terms-and-conditions', function() {
    include 'TermsAndConditions.php';
  });

  $this->post('/terms-and-conditions', function() {
    include 'TermsAndConditionsPost.php';
  });
}

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Stage'] == 'CodeOfConduct') {
  $this->get(['/code-of-conduct', '/code-of-conduct/{name}'], function($name = null) {
    include 'ConductAgreement.php';
  });

  $this->post('/code-of-conduct', function() {
    include 'ConductAgreementPost.php';
  });
}

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Stage'] == 'AutoAccountSetup' || true) {
  $this->get('/setup-account', function() {
    include 'AutoAccountSetup.php';
  });
}

	$this->group('/payments', function() {
		$this->get(['/setup', '/setup/{stage}:int'], function($stage = 0) {
			
			$renewal_trap = true;
			require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';
			if ($stage == 0) {
				require(BASE_PATH . 'controllers/payments/setup/start.php');
			}
			else if ($stage == 1) {
				require(BASE_PATH . 'controllers/payments/setup/date.php');
			}
			else if ($stage == 2) {
				require(BASE_PATH . 'controllers/payments/setup/initiate.php');
			}
			else if ($stage == 3) {
				require(BASE_PATH . 'controllers/payments/setup/redirect.php');
			}
		});
		$this->post('/setup/1', function() {
			
			include BASE_PATH . 'controllers/payments/setup/datepost.php';
		});
	});

	$this->group('/emergencycontacts', function() {
		$this->get(['/'], function() {
			
			$renewal_trap = true;
			include BASE_PATH . 'controllers/emergencycontacts/parents/index.php';
		});
		$this->get('/edit/{id}:int', function($id) {
			
			$renewal_trap = true;
			require('controllers/emergencycontacts/parents/edit.php');
		});
		$this->post('/edit/{id}:int', function($id) {
			
			$renewal_trap = true;
			require('controllers/emergencycontacts/parents/editUpdate.php');
		});
		$this->get('/new', function() {
			
			$renewal_trap = true;
			require('controllers/emergencycontacts/parents/new.php');
		});
		$this->post('/new', function() {
			
			$renewal_trap = true;
			require('controllers/emergencycontacts/parents/newAction.php');
		});
		$this->get('/{id}:int/delete', function($id) {
			
			$renewal_trap = true;
			require('controllers/emergencycontacts/parents/delete.php');
		});
	});

*/
	
/**
 * Present users with splash screen
 */

 /* 
$this->get('/{hash}', function($hash) {
  include 'BeginRegistration.php';
});

$this->post('/{hash}', function($hash) {
  include 'BeginRegistrationPost.php';
});
*/