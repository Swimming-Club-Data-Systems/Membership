<?php

$db = app()->db;
$tenant = app()->tenant;

$currentUser = null;

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) && isset($_SESSION['SCDS-SuperUser'])) {
  try {
    // Sign in from super user
    $getSuperUser = $db->prepare("SELECT Email FROM superUsers WHERE ID = ?");
    $getSuperUser->execute([
      $_SESSION['SCDS-SuperUser']
    ]);
    $email = $getSuperUser->fetchColumn();

    if ($email == null) throw new Exception('No superuser');

    // Get matching user
    $getUser = $db->prepare("SELECT UserID FROM users WHERE EmailAddress = ? AND Tenant = ? AND Active = ?");
    $getUser->execute([
      $email,
      $tenant->getId(),
      (int) true,
    ]);
    $user = $getUser->fetchColumn();

    if ($user == null) throw new Exception('No user');

    $login = new \CLSASC\Membership\Login($db);
    $login->setUser($user);
    // $login->stayLoggedIn();
    $login->preventWarningEmail();
    app()->user = $login->login();
  } catch (Exception $e) {
    // Ignore
  }
} else if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) && isset($_COOKIE[COOKIE_PREFIX . 'SUPERUSER-AutoLogin'])) {
  // Ignore for now
}

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) && isset($_COOKIE[COOKIE_PREFIX . 'TENANT-' . app()->tenant->getId() . '-' . 'AutoLogin']) && $_COOKIE[COOKIE_PREFIX . 'TENANT-' . app()->tenant->getId() . '-' . 'AutoLogin'] != "") {
  $sql = "SELECT users.UserID, `Time` FROM `userLogins` INNER JOIN users ON users.UserID = userLogins.UserID WHERE users.Tenant = ? AND `Hash` = ? AND `Time` >= ? AND `HashActive` = ?";

  $data = [
    $tenant->getId(),
    $_COOKIE[COOKIE_PREFIX . 'TENANT-' . app()->tenant->getId() . '-' . 'AutoLogin'],
    date('Y-m-d H:i:s', strtotime("120 days ago")),
    1
  ];

  try {
    $query = $db->prepare($sql);
    $query->execute($data);
  } catch (PDOException $e) {
    //halt(500);
  }

  $row = $query->fetch(PDO::FETCH_ASSOC);
  if ($row != null) {
    $user = $row['UserID'];
    $utc = new DateTimeZone("UTC");
    $time = new DateTime($row['Time'], $utc);

    try {
      $login = new \CLSASC\Membership\Login($db);
      $login->setUser($user);
      $login->stayLoggedIn();
      $login->preventWarningEmail();
      $login->reLogin();
      app()->user = $login->login();
    } catch (Exception $e) {
      reportError($e);
      // halt(403);
    }

    $hash = hash('sha512', time() . $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'] . random_bytes(64));

    $sql = "UPDATE `userLogins` SET `Hash` = ? WHERE `Hash` = ?";
    try {
      $query = $db->prepare($sql);
      $query->execute([$hash, $_COOKIE['TENANT-' . app()->tenant->getId() . '-' . 'AutoLogin']]);
    } catch (PDOException $e) {
      halt(500);
    }

    $expiry_time = ($time->format('U')) + 60 * 60 * 24 * 120;

    $secure = true;
    if (app('request')->protocol == 'http' && bool(getenv('IS_DEV'))) {
      $secure = false;
    }
    $cookiePath = '/' . app()->tenant->getCodeId();
    if (getenv('MAIN_DOMAIN')) {
      $cookiePath = '';
    }
    setcookie('TENANT-' . app()->tenant->getId() . '-' . "AutoLogin", $hash, $expiry_time, $cookiePath, app('request')->hostname, $secure, false);
  }
} else if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) {
  app()->user = new User($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], true);
}

// Log urls
if (isset(app()->user) && app()->user) {
  try {
    // pre(app()->request);
    $path = substr(app()->request->path, strlen('/' . app()->tenant->getCodeId()), strlen(app()->request->path) - strlen('/' . app()->tenant->getCodeId()));
    if (substr($path, 0, 7) !== "/public" && substr($path, 0, 8) !== "/uploads" && substr($path, 0, 6) !== "/sw.js") {
      AuditLog::new('HTTP_REQUEST', app()->request->method . ' - ' . app()->request->curl);
    }
  } catch (Exception $e) {
    // Ignore all errors
  }
}

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) && $_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'] && !isset($_SESSION['TENANT-' . app()->tenant->getId()]['DisableTrackers'])) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['DisableTrackers'] = filter_var(getUserOption($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], "DisableTrackers"), FILTER_VALIDATE_BOOLEAN);
}

if (bool(getenv('IS_DEV'))) {
  $this->group('/dev', function () {
    include BASE_PATH . 'controllers/dev/router.php';
  });
}

if (isset($_SESSION['OnboardingSessionId'])) {
  $this->group('/renewal', function () {
    include BASE_PATH . 'controllers/renewal/router.php';
  });
}

$this->get('/auth/cookie/redirect', function () {
  //$target = urldecode($target);
  setcookie('TENANT-' . app()->tenant->getId() . '-' . "SeenAccount", true, 0, "/", app('request')->hostname, true, false);
  header("Location: https://www.chesterlestreetasc.co.uk");
});

// PWA Stuff
$this->get('/manifest.webmanifest', function () {
  include BASE_PATH . 'controllers/pwa/manifest.php';
});

$this->group('/pwa', function () {
  include BASE_PATH . 'controllers/pwa/router.php';
});

$this->get('/sw.js', function () {
  $filename = 'js/service-workers/sw.js';
  require BASE_PATH . 'controllers/PublicFileLoader.php';
});

$this->group('/js', function () {
  include BASE_PATH . 'dynamic-javascript/router.php';
});

$this->get('/emergency-message.json', function () {
  include BASE_PATH . 'controllers/public/emergency-message.json.php';
});

$this->post('/check-login.json', function () {
  header("content-type: application/json");
  echo json_encode(['signed_in' => isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])]);
});

$this->get('/public/css/colour.css', function () {
  require BASE_PATH . 'public/css/colour.css';
});

$this->get('/public/*', function () {
  $array = $this->getArrayCopy();
  $filename = $array[sizeof($array) - 1];
  // pre($filename);

  require BASE_PATH . 'controllers/PublicFileLoader.php';
});

$this->get('/uploads/*', function () {
  $array = $this->getArrayCopy();
  $filename = 'public/' . $array[sizeof($array) - 1];
  // $filename = 'public/' . $this[0];
  require BASE_PATH . 'controllers/FileLoader.php';
});

if (getenv('MAINTENANCE')) {
  $this->any(['/', '/*'], function () {
    halt(000);
  });
}

$this->get('/setup', function () {
  include BASE_PATH . 'controllers/db/system-setup.php';
});

$this->group(['/sessions', '/timetable'], function () {
  include BASE_PATH . 'controllers/attendance/public_sessions/router-public.php';
});

$this->group(['/contact-tracing', '/covid/contact-tracing'], function () {
  include BASE_PATH . 'controllers/contact-tracing/router.php';
});

$this->get('/log-book', function () {
  http_response_code(303);
  header("location: " . autoUrl("log-books"));
});

// Password Reset via Link
$this->get('/verify-cc-email/auth/{id}:int/{hash}', function ($id, $hash) {
  include BASE_PATH . 'controllers/myaccount/CC/verify.php';
});

// Password Reset via Link
$this->get('/email/auth/{id}:int/{auth}', function ($id, $auth) {

  include BASE_PATH . 'controllers/myaccount/EmailUpdate.php';
});

// Link Accounts
$this->get('/linked-accounts/auth/{id}:int/{key}', function ($id, $key) {
  include BASE_PATH . 'controllers/myaccount/linked-accounts/NewConfirm.php';
});

$this->get('/notify/unsubscribe/{userid}/{email}/{list}', function ($userid, $email, $list) {

  include BASE_PATH . 'controllers/notify/UnsubscribeHandlerAsk.php';
});

$this->get('/notify/unsubscribe/{userid}/{email}/{list}/do', function ($userid, $email, $list) {

  include BASE_PATH . 'controllers/notify/UnsubscribeHandler.php';
});

$this->get(['/help-and-support', '/help-and-support/*'], function () {
  include BASE_PATH . 'controllers/help/help-documentation.php';
});

$this->group(['/timeconverter', '/time-converter'], function () {
  $this->get('/', function () {
    include BASE_PATH . 'controllers/conversionsystem/testing.php';
  });

  $this->post('/', function () {
    include BASE_PATH . 'controllers/conversionsystem/PostTesting.php';
  });
});

$this->group('/onboarding/go', function () {
  include BASE_PATH . 'controllers/onboarding/public/router.php';
});

$this->get('/reportanissue', function () {
  include BASE_PATH . 'controllers/help/ReportIssueHandler.php';
});
$this->post('/reportanissue', function () {
  include BASE_PATH . 'controllers/help/ReportIssuePost.php';
});

$this->group('/ajax', function () {
  include BASE_PATH . 'controllers/public/router.php';
});

$this->group('/views', function () {
  include BASE_PATH . 'views/routes/router.php';
});

$this->group('/ajax-utilities', function () {
  include BASE_PATH . 'controllers/ajax/router.php';
});

$this->get('/privacy', function () {
  include BASE_PATH . 'controllers/posts/privacy.php';
});

$this->get('/cc/{id}/{hash}/unsubscribe', function ($id, $hash) {
  include BASE_PATH . 'controllers/notify/CCUnsubscribe.php';
});

$this->group('/payments/checkout', function () {
  include 'checkout.php';
});

$this->group('/services', function () {
  $this->get('/barcode-generator', function () {
    include BASE_PATH . 'controllers/barcode-generation-system/gen.php';
  });

  $this->get('/qr/{number}:int/{sizeurl}?:int', function ($number, $size_url) {
    include BASE_PATH . 'controllers/barcode-generation-system/qr-safe.php';
  });

  $this->get('/qr-generator', function () {
    include BASE_PATH . 'controllers/barcode-generation-system/qr.php';
  });

  include BASE_PATH . 'controllers/services/router.php';
});

// Log out
$this->any(['/logout', '/logout.php'], function () {
  include BASE_PATH . 'controllers/logout.php';
});

// if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR']) && $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR']) {
//   $this->group('/2fa', function () {
//     $this->get('/', function () {
//       include BASE_PATH . 'views/TwoFactorCodeInput.php';
//     });

//     $this->post('/', function () {
//       include BASE_PATH . 'controllers/2fa/SubmitCode.php';
//     });

//     $this->get('/exit', function () {
//       $_SESSION = [];
//       unset($_SESSION);
//       header("Location: " . autoUrl("login"));
//     });

//     $this->get('/resend', function () {
//       include BASE_PATH . 'controllers/2fa/ResendCode.php';
//     });
//   });

//   $this->get(['/', '/*'], function () {
//     $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR'] = true;
//     header("Location: " . autoUrl("2fa"));
//   });
// }

$this->group('/oauth2', function () {

  $this->any('/authorize', function () {
    include BASE_PATH . 'controllers/oauth/AuthorizeController.php';
  });

  $this->any('/token', function () {
    include BASE_PATH . 'controllers/oauth/TokenController.php';
  });

  $this->get('/userinfo', function () {
    include BASE_PATH . 'controllers/oauth/UserDetails.php';
  });
});

if (empty($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) {
  // $this->post('/login', function () {
  //   include BASE_PATH . 'controllers/login-go.php';
  // });

  // Home
  // $this->get('/', function () {
  //   include BASE_PATH . "views/Welcome.php";
  // });

  // $this->get('/login', function () {
  //   http_response_code(303);
  //   header("Location: " . autoUrl("login?club=" . mb_strtolower(app()->tenant->getCodeId()), false));
  // });

  // $this->get('/login', function () {
  //   include BASE_PATH . 'views/Login.php';
  // });

  $this->get('/login/oauth', function () {
    include BASE_PATH . 'controllers/oauth-login.php';
  });

  // $this->group('/about', function () {
  //   include BASE_PATH . 'controllers/about/router.php';
  // });

  // // Register
  // $this->get(['/register', '/register/family', '/register/family/{fam}:int/{acs}:key'], function ($fam = null, $acs = null) {
  //   include BASE_PATH . 'controllers/registration/register.php';
  // });

  // $this->group(['/register/ac'], function () {
  //   include BASE_PATH . 'controllers/registration/join-from-trial/router.php';
  // });

  // $this->post('/register', function () {
  //   include BASE_PATH . 'controllers/registration/registration.php';
  // });

  // // Confirm Email via Link
  // $this->get('/register/auth/{id}:int/new-user/{token}', function ($id, $token) {

  //   include BASE_PATH . 'controllers/registration/RegAuth.php';
  // });

  // Locked Out Password Reset
  // $this->get('/resetpassword', function () {

  //   include BASE_PATH . 'controllers/forgot-password/request.php';
  // });

  // $this->post('/resetpassword', function () {

  //   include BASE_PATH . 'controllers/forgot-password/request-action.php';
  // });

  // Password Reset via Link
  // $this->get('/resetpassword/auth/{token}', function ($token) {

  //   include BASE_PATH . 'controllers/forgot-password/reset.php';
  // });

  // $this->post('/resetpassword/auth/{token}', function ($token) {

  //   include BASE_PATH . 'controllers/forgot-password/reset-action.php';
  // });

  $this->group('/payments/webhooks', function () {

    include BASE_PATH . 'controllers/payments/webhooks.php';
  });

  $this->any('/payments/stripe/webhooks', function () {
    include BASE_PATH . 'controllers/payments/stripe/webhooks.php';
  });

  $this->group('/webhooks', function () {
    include BASE_PATH . 'controllers/webhooks/router.php';
  });

  $this->get('/notify', function () {
    include BASE_PATH . 'controllers/notify/Help.php';
  });

  $this->group('/log-books', function () {
    include BASE_PATH . 'controllers/log-books/router.php';
  });

  /*
  $this->get('/files/*', function() {
    $filename = $this[0];
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
      header('WWW-Authenticate: Basic realm="Use your normal account Email Address and Password"');
      halt(401);
    } else if (verifyUser($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
      require BASE_PATH . 'controllers/FileLoader.php';
    } else {
      header('WWW-Authenticate: Basic realm="Use your normal account Email Address and Password"');
      halt(401);
    }
  });
  */

  // Global Catch All send to login
  // $this->any(['/', '/*'], function () {
  //   http_response_code(303);
  //   header("Location: " . autoUrl("login?club=" . mb_strtolower(app()->tenant->getCodeId() . '&target=' . urlencode(app('request')->path)), false));
  // });

  $this->group('/api', function () {
    include BASE_PATH . 'controllers/api/public-api.php';
  });

  // Global Catch All 404
  $this->get(['/', '/*'], function () {
    include BASE_PATH . 'views/react-public-base.php';
  });

  // $this->any(['/', '/*'], function () {
  //   http_response_code(303);
  //   header("Location: " . autoUrl('login?target=' . urlencode(app('request')->path)));
  // });
} else if (user_needs_registration($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) {
  $this->group('/renewal', function () {
    include BASE_PATH . 'controllers/renewal/router.php';
  });

  $this->get('/account-switch', function () {
    include BASE_PATH . 'controllers/account-switch.php';
  });

  $this->group('/registration', function () {
    include BASE_PATH . 'controllers/registration/router.php';
  });

  $this->group('/users', function () {
    include BASE_PATH . 'controllers/users/router.php';
  });

  $this->any(['/', '/*'], function () {
    header("Location: " . autoUrl("registration"));
  });
} else {
  // Home

  if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
    $this->get('/', function () {

      include BASE_PATH . 'controllers/ParentDashboard.php';
    });
  } else {
    $this->get('/', function () {

      include BASE_PATH . 'controllers/NewDashboard.php';
    });
  }

  $this->get('/account-switch', function () {
    include BASE_PATH . 'controllers/account-switch.php';
  });

  $this->get('/login', function () {
    header("Location: " . autoUrl(""));
  });

  $this->group(['/my-account', '/myaccount'], function () {

    include BASE_PATH . 'controllers/myaccount/router.php';
  });

  $this->group(['/swimmers', '/members', '/divers', '/water-polo-players'], function () {
    include BASE_PATH . 'controllers/swimmers/router.php';
  });

  // Temporary tools and features for covid
  $this->group('/covid', function () {
    include BASE_PATH . 'controllers/covid/router.php';
  });

  $this->group('/api', function () {
    include BASE_PATH . 'controllers/api/api.php';
  });

  $this->group('/squads', function () {
    include BASE_PATH . 'controllers/squads/router.php';
  });

  $this->group('/squad-reps', function () {
    include BASE_PATH . 'controllers/squads/squad-reps/router.php';
  });

  $this->group(['/sessions', '/timetable'], function () {
    include BASE_PATH . 'controllers/attendance/public_sessions/router.php';
  });

  /**
   * CORONAVIRUS (COVID-19) EMERGENCY MESSAGE
   */
  $this->get('/emergency-message', function () {
    header("location: " . autoUrl("settings/variables#emergency-message"));
  });

  $this->get('/team-managers', function () {
    include BASE_PATH . 'controllers/galas/squad-reps-and-team-managers/team-manager-event-list.php';
  });


  $this->group('/onboarding', function () {
    include BASE_PATH . 'controllers/onboarding/router.php';
  });

  if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Parent") {
    $this->group('/trials', function () {
      include BASE_PATH . 'controllers/trials/router.php';
    });
  }

  $this->group(['/posts', '/pages'], function () {
    include BASE_PATH . 'controllers/posts/router.php';
  });

  // $this->group('/file-manager', function () {
  //   include BASE_PATH . 'controllers/file-manager/router.php';
  // });

  $this->group('/registration', function () {
    include BASE_PATH . 'controllers/registration/router.php';
  });

  $this->group('/memberships', function () {
    include BASE_PATH . 'controllers/memberships/router.php';
  });

  $this->group(['/attendance', '/registers'], function () {
    include BASE_PATH . 'controllers/attendance/router.php';
  });

  $this->group('/users', function () {
    include BASE_PATH . 'controllers/users/router.php';
  });

  $this->group('/admin', function () {
    include BASE_PATH . 'controllers/admin-tools/router.php';
  });

  $this->group('/tick-sheets', function () {
    include BASE_PATH . 'controllers/tick-sheets/router.php';
  });

  $this->group('/galas', function () {
    include BASE_PATH . 'controllers/galas/router.php';
  });

  // $this->group('/family', function () {
  //   include BASE_PATH . 'controllers/family/router.php';
  // });

  $this->group('/renewal', function () {
    include BASE_PATH . 'controllers/renewal/router.php';
  });

  $this->group('/registration', function () {
    include BASE_PATH . 'controllers/registration/router.php';
  });

  $this->group('/form-agreement', function () {
    include BASE_PATH . 'controllers/forms/router.php';
  });

  $this->group('/notify', function () {

    include BASE_PATH . 'controllers/notify/router.php';
  });

  $this->group('/log-books', function () {
    include BASE_PATH . 'controllers/log-books/router.php';
  });

  $this->group(['/emergency-contacts', '/emergencycontacts'], function () {
    include BASE_PATH . 'controllers/emergencycontacts/router.php';
  });

  $this->any('/login/oauth', function () {
    header('Location: ' . autoUrl(''));
  });

  $this->group('/payments', function () {
    include BASE_PATH . 'controllers/payments/router.php';
  });

  $this->group('/webhooks', function () {
    $this->group('/payments', function () {
      include BASE_PATH . 'controllers/payments/webhooks.php';
    });
  });

  $this->group('/qualifications', function () {
    include BASE_PATH . 'controllers/qualifications/router.php';
  });

  $this->group('/assisted-registration', function () {
    $this->get(['/', '/*'], function () {
      http_response_code(302);
      header("location: " . autoUrl("onboarding/new"));
    });
  });

  $this->group('/resources', function () {
    include BASE_PATH . 'controllers/resources/router.php';
  });

  $this->group('/tenant-services', function () {
    include BASE_PATH . 'controllers/tenant-services/routes.php';
  });

  if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin") {
    $this->group('/settings', function () {
      include BASE_PATH . 'controllers/settings/router.php';
    });

    if (bool(getenv('IS_DEV'))) {
      $this->get('/about:php', function () {
        echo phpinfo();
      });

      $this->get('/about:session', function () {
        pre($_SESSION);
      });

      $this->get('/about:server', function () {
        pre($_SERVER);
        pre($_ENV);
      });

      $this->get('/about:cookies', function () {
        pre($_COOKIE);
      });

      $this->get('/about:stopcodes/{code}:int', function ($code) {
        halt((int) $code);
      });

      $this->get('/pdf-test', function () {
        include BASE_PATH . 'controllers/PDFTest.php';
      });

      /*
      $this->get('/test', function() {
        //use \Twilio\Rest\Client;

        try {
          // Your Account SID and Auth Token from twilio.com/console
          $account_sid = getenv('TWILIO_AC_SID');
          $auth_token = getenv('TWILIO_AC_AUTH_TOKEN');
          // In production, these should be environment variables. E.g.:
          // $auth_token = $_ENV["TWILIO_ACCOUNT_SID"]

          // A Twilio number you own with SMS capabilities
          $twilio_number = getenv('TWILIO_NUMBER');

          $client = new Twilio\Rest\Client($account_sid, $auth_token);
          $client->messages->create(
            // Where to send a text message (your cell phone?)
            '+447577002981',
            [
              'from' => $twilio_number,
              'body' => 'I sent this message in under 10 minutes!'
            ]
          );
        } catch (Exception $e) {
          pre($e);
        }
      });
      */
    }
  }
}

$this->get('/files/*', function () {
  $array = $this->getArrayCopy();
  $filename = $array[sizeof($array) - 1];
  require BASE_PATH . 'controllers/FileLoader.php';
});

// Global Catch All 404
$this->any('/', function () {
  header("Location: " . autoUrl(""));
});

// Global Catch All 404
$this->get('/*', function () {
  include BASE_PATH . 'views/react-base.php';
});

$this->any('/*', function () {
  include BASE_PATH . 'views/404.php';
});
