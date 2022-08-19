<?php

if (getenv('IS_DEV')) {
  // ----------------------------------------------------------------------------------------------------
  // - Display Errors
  // ----------------------------------------------------------------------------------------------------
  ini_set('display_errors', 'On');
  ini_set('html_errors', 0);

  // ----------------------------------------------------------------------------------------------------
  // - Error Reporting
  // ----------------------------------------------------------------------------------------------------
  error_reporting(-1);

  // ----------------------------------------------------------------------------------------------------
  // - Shutdown Handler
  // ----------------------------------------------------------------------------------------------------
  function ShutdownHandler()
  {
    if (@is_array($error = @error_get_last())) {
      return (@call_user_func_array('ErrorHandler', $error));
    };

    return (TRUE);
  };

  register_shutdown_function('ShutdownHandler');

  // ----------------------------------------------------------------------------------------------------
  // - Error Handler
  // ----------------------------------------------------------------------------------------------------
  function ErrorHandler($type, $message, $file, $line)
  {
    $_ERRORS = array(
      0x0001 => 'E_ERROR',
      0x0002 => 'E_WARNING',
      0x0004 => 'E_PARSE',
      0x0008 => 'E_NOTICE',
      0x0010 => 'E_CORE_ERROR',
      0x0020 => 'E_CORE_WARNING',
      0x0040 => 'E_COMPILE_ERROR',
      0x0080 => 'E_COMPILE_WARNING',
      0x0100 => 'E_USER_ERROR',
      0x0200 => 'E_USER_WARNING',
      0x0400 => 'E_USER_NOTICE',
      0x0800 => 'E_STRICT',
      0x1000 => 'E_RECOVERABLE_ERROR',
      0x2000 => 'E_DEPRECATED',
      0x4000 => 'E_USER_DEPRECATED'
    );

    if (!@is_string($name = @array_search($type, @array_flip($_ERRORS)))) {
      $name = 'E_UNKNOWN';
    };

    return (print(@sprintf("%s Error in file \xBB%s\xAB at line %d: %s\n", $name, ($file), $line, $message)));
  };

  $old_error_handler = set_error_handler("ErrorHandler");
}

// This line fixes some things
$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];

// Do not reveal PHP when sending mail
ini_set('expose_php', 'Off');

$time_start = microtime(true);

$executionStartTime = microtime();

define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATH', __DIR__ . DS);

require BASE_PATH . 'vendor/autoload.php';
require "helperclasses/ClassLoader.php";
require "classes-loader.php";

if (getenv('ENV_JSON_FILE')) {
  require 'common/env/loader.php';
}

$_SERVER['SERVER_PORT'] = 443;

if (getenv('COOKIE_PREFIX')) {
  define('COOKIE_PREFIX', getenv('COOKIE_PREFIX'));
} else {
  define('COOKIE_PREFIX', 'SCDS_MEMBERSHIP_SYSTEMS_');
}

if (getenv('COOKIE_PATH')) {
  define('COOKIE_PATH', getenv('COOKIE_PATH'));
} else {
  define('COOKIE_PATH', '/');
}

if (getenv('CACHE_DIR')) {
  define('CACHE_DIR', getenv('CACHE_DIR'));
} else {
  define('CACHE_DIR', BASE_PATH . 'cache/');
}

// Special STUFF

define('UOS_RETURN_FORM_NAME', 'Sport Sheffield Return to Training Form');

// END

use Symfony\Component\DomCrawler\Crawler;
use GeoIp2\Database\Reader;

$app            = System\App::instance();
$app->request   = System\Request::instance();
$app->route     = System\Route::instance($app->request);

$route          = $app->route;

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

//halt(901);

/*
if (!(sizeof($_SESSION) > 0)) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['TARGET_URL'] = app('request')->curl;
}
*/

app()->locale = 'en_GB';
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
  try {
    app()->locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
  } catch (Exception $e) {
    app()->locale = 'en_GB';
  }
}

function currentUrl()
{
  $uri = ltrim($_SERVER["REQUEST_URI"], '/');
  $url = autoUrl($uri);
  if (mb_substr($url, -1) != '/') {
    $url = $url . '/';
  }
  return $url;
}

// This code is required so cookies work in dev environments
$cookieSecure = 1;
if (app('request')->protocol == 'http') {
  $cookieSecure = 0;
}

if (getenv('MAIN_DOMAIN') && getenv('DOMAIN_TYPE') == 'SUBDOMAIN') {
  // DOMAIN SESSION
  session_start([
    //'cookie_lifetime' => 172800,
    'gc_maxlifetime'      => 3600,
    'cookie_httponly'     => 0,
    'gc_probability'      => 1,
    'use_only_cookies'    => 1,
    'cookie_secure'       => $cookieSecure,
    'use_strict_mode'     => 1,
    'sid_length'          => 128,
    'name'                => COOKIE_PREFIX . 'TenantSessionId',
    'cookie_domain'       => $_SERVER['HTTP_HOST'],
    'cookie_path'         => COOKIE_PATH,
  ]);
} else {
  // OTHER
  session_start([
    //'cookie_lifetime' => 172800,
    'gc_maxlifetime'      => 3600,
    'cookie_httponly'     => 0,
    'gc_probability'      => 1,
    'use_only_cookies'    => 1,
    'cookie_secure'       => $cookieSecure,
    'use_strict_mode'     => 1,
    'sid_length'          => 128,
    'name'                => COOKIE_PREFIX . 'GlobalSessionId',
    'cookie_domain'       => $_SERVER['HTTP_HOST'],
    'cookie_path'         => COOKIE_PATH,
  ]);
}

function halt(int $statusCode, $throwException = true)
{
  try {
    ob_get_clean();
  } catch (Exception | Error $e) {
    // Can't clean ob
  }

  try {
    if ($statusCode == 000) {
      require "views/000.php";
    } else if ($statusCode == 200) {
      require "views/200.php";
    } else if ($statusCode == 400) {
      require "views/400.php";
    } else if ($statusCode == 401) {
      require "views/401.php";
    } else if ($statusCode == 403) {
      require "views/403.php";
    } else if ($statusCode == 404) {
      if (isset(app()->tenant)) {
        require "views/404.php";
      } else {
        require "views/root/errors/404.php";
      }
    } else if ($statusCode == 503) {
      require "views/503.php";
    } else if ($statusCode == 0) {
      require "views/000.php";
    } else if ($statusCode == 900) {
      // Unavailable for Regulatory Reasons
      require "views/900.php";
    } else if ($statusCode == 901) {
      // Unavailable due to GDPR
      require "views/901.php";
    } else if ($statusCode == 902) {
      // Unavailable due to no GC API Key
      require "views/902.php";
    } else if ($statusCode == 999) {
      // Show last report 500 error
      require 'views/LastResort500.php';
    } else {
      if (isset(app()->tenant)) {
        require "views/500.php";
      } else {
        require "views/root/errors/500.php";
      }
    }
  } catch (Exception | Error $e) {
    // Show emergency plain error page
    ob_get_clean();

    require 'views/LastResort500.php';
  }

  if ($throwException) {
    throw new \SCDS\HaltException('Status ' . $statusCode);
  }
}

$db = null;
try {
  $db = new PDO("mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8mb4", getenv('DB_USER'), getenv('DB_PASS'));
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
  halt(500);
}

$path = realpath(BASE_PATH . '../');
$headInfo = explode(' ', file_get_contents($path . '/.git/HEAD'));
if ($headInfo[0] == 'ref:') {
  $HEAD_hash = file_get_contents($path . '/.git/' . trim($headInfo[1]));
  define('SOFTWARE_VERSION', $HEAD_hash);
  define('SOFTWARE_BRANCH', trim(mb_substr($headInfo[1], 11, mb_strlen($headInfo[1]) - 11)));
}

require_once "functions.php";

if (!isset($_SESSION['Browser'])) {
  $browser = new \WhichBrowser\Parser(getallheaders());

  // reportError($browser);

  if (isset($browser->browser->name) && $browser->browser->name) {
    $_SESSION['Browser']['Name'] = $browser->browser->name;
  } else {
    $_SESSION['Browser']['Name'] = 'Unknown Browser';
  }

  if (isset($browser->os) && $browser->os->toString()) {
    $_SESSION['Browser']['OS'] = $browser->os->toString();
  } else {
    $_SESSION['Browser']['OS'] = 'Unknown OS';
  }

  if (isset($browser->browser->version->value) && $browser->browser->version->value) {
    $_SESSION['Browser']['Version'] = $browser->browser->version->value;
  } else {
    $_SESSION['Browser']['Version'] = 'Unknown Browser Version';
  }

  if (isset($browser->os->version->value) && $browser->os->version->value) {
    $_SESSION['Browser']['OSVersion'] = $browser->os->version->value;
  } else {
    $_SESSION['Browser']['OSVersion'] = null;
  }

  if (isset($browser->os->name) && $browser->os->name) {
    $_SESSION['Browser']['OSName'] = $browser->os->name;
  } else {
    $_SESSION['Browser']['OSName'] = null;
  }
}

// Make db available
$db = null;
try {
  $db = new PDO("mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8mb4", getenv('DB_USER'), getenv('DB_PASS'));
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
  halt(500);
}

app()->db = $db;

if (getenv('MAIN_DOMAIN') && getenv('DOMAIN_TYPE') == 'SUBDOMAIN') {
  if (!isset($_SESSION['SCDS-SuperUser']) && isset($_COOKIE[COOKIE_PREFIX . 'SUPERUSER-AutoLogin']) && $_COOKIE[COOKIE_PREFIX . 'SUPERUSER-AutoLogin'] != "") {

    $date = new DateTime('120 days ago', new DateTimeZone('UTC'));

    $data = [
      $_COOKIE[COOKIE_PREFIX . 'SUPERUSER-AutoLogin'],
      $date->format('Y-m-d H:i:s'),
      1
    ];

    try {
      $query = $db->prepare("SELECT superUsers.ID, `Time` FROM `superUsersLogins` INNER JOIN superUsers ON superUsers.ID = superUsersLogins.User WHERE `Hash` = ? AND `Time` >= ? AND `HashActive` = ?");
      $query->execute($data);
    } catch (PDOException $e) {
      //halt(500);
    }

    $row = $query->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      $user = $row['ID'];
      $time = new DateTime($row['Time'], new DateTimeZone("UTC"));

      $_SESSION['SCDS-SuperUser'] = $user;

      $hash = hash('sha512', time() . $user . '-' . random_bytes(128));

      try {
        $query = $db->prepare("UPDATE `superUsersLogins` SET `Hash` = ? WHERE `Hash` = ?");
        $query->execute([$hash, $_COOKIE[COOKIE_PREFIX . 'SUPERUSER-AutoLogin']]);
      } catch (PDOException $e) {
        halt(500);
      }

      $expiry_time = ($time->format('U')) + 60 * 60 * 24 * 120;

      $secure = true;
      if (app('request')->protocol == 'http' && bool(getenv('IS_DEV'))) {
        $secure = false;
      }
      $cookiePath = '/';
      setcookie(COOKIE_PREFIX . 'SUPERUSER-AutoLogin', $hash, $expiry_time, $cookiePath, getenv('MAIN_DOMAIN'), $secure, false);
    }
  }
}

// System info stuff
// $systemInfo = new \SystemInfo($db);
// app()->system = $systemInfo;

// Load vars
// include BASE_PATH . 'includes/GetVars.php';

// User login if required and make user var available

$route->addPattern([
  'uuid' => '/([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}',
]);

$route->use(function () {
  // Make req available
  $req = app('request');

  header("Permissions-Policy: fullscreen 'self' https://youtube.com");
  header("Referrer-Policy: strict-origin-when-cross-origin");
  header("Content-Security-Policy: block-all-mixed-content");
  // Prevent framing of the membership system
  header("X-Frame-Options: DENY");
  // Prevent MIME sniffing
  header("X-Content-Type-Options: nosniff");
  header('Permissions-Policy: interest-cohort=()');
});

// $route->route(['GET'], '/*', function () {
//   pre("TESTING");
//   $this->matched = true;
// }, [
//   'continue' => true
// ]);

// If SUMDOMAIN OR DOMAIN
if (getenv('MAIN_DOMAIN') && getenv('DOMAIN_TYPE') == 'SUBDOMAIN') {
  // Else use main domain
  // Get the club

  // Check for tenant redirects
  $checkRedirect = $db->prepare("SELECT `Tenant`, `Target` FROM `tenantRedirect` WHERE `Source` = ? ORDER BY `Created` ASC;");
  $checkRedirect->execute([
    app('request')->hostname
  ]);

  if ($redirect = $checkRedirect->fetch(PDO::FETCH_OBJ)) {

    http_response_code(302);
    if ($redirect->Target) {
      $route->any(['/', '/*'], function () {
        global $redirect;
        header('location: ' . $redirect->Target);
      });
    } else {
      $clubObject = Tenant::fromId($redirect->Tenant);
      if ($clubObject) {
        $link = $clubObject->getDomain();
        if (!$link) {
          $link = $clubObject->getUuid() . '.' . getenv('MAIN_DOMAIN');
        }
        $link = 'https://' . $link . app()->request->path;
        $route->any(['/', '/*'], function () {
          global $link;
          header('location: ' . $link);
        });
      } else {
        $route->any(['/', '/*'], function () {
          header('location: https://myswimmingclub.uk');
        });
      }
    }
  } else {

    $clubObject = Tenant::fromDomain(app('request')->hostname);

    if (!$clubObject) {
      $uuid = str_replace('.' . getenv('MAIN_DOMAIN'), '', app('request')->hostname);
      $clubObject = Tenant::fromUUID($uuid);
    }

    if (!$clubObject) {
      // Send to main page
      $route->any(['/', '/*'], function () {
        http_response_code(302);
        header('location: https://myswimmingclub.uk');
      });
    } else {
      app()->club = $clubObject->getCodeID();
      app()->tenant = $clubObject;

      // pre($clubObject);

      // $route->get('/', function() {
      //   pre(app()->request);
      // });

      $route->group('/', function () {
        include BASE_PATH . 'routes/club/routes.php';
      });
    }
  }
} else {
  $route->group('/{club}:int', function ($club) {

    if ($club) {
      // Get the club
      $clubObject = Tenant::fromId((int) $club);

      if (!$clubObject) {
        define('CLUB_PROVIDED', $club);
        $this->any(['/', '/*'], function () {
          $club = CLUB_PROVIDED;
          include 'views/root/errors/no-club.php';
        });
      } else {
        app()->club = $club;
        app()->tenant = $clubObject;

        include BASE_PATH . 'routes/club/webhook-routes.php';
      }
    }
  });

  $route->group('/{club}:([a-z]{4})', function ($club) {

    if ($club) {
      // Get the club
      $clubObject = Tenant::fromCode($club);

      if (!$clubObject) {
        define('CLUB_PROVIDED', $club);
        $this->any(['/', '/*'], function () {
          $club = CLUB_PROVIDED;
          include 'views/root/errors/no-club.php';
        });
      } else {
        app()->club = $club;
        app()->tenant = $clubObject;

        include BASE_PATH . 'routes/club/webhook-routes.php';
      }
    }
  });

  // $route->get('/migrate', function () {
  //   include 'controllers/db-increaseIds.php';
  // });

  // $route->get('/testing', function () {
  //   include 'controllers/dev/times.php';
  // });

  $route->group('/', function () {
    include 'routes/root/routes.php';
  });
}


try {
  ob_get_clean();
  $route->end();
  echo ob_get_clean();
} catch (\SCDS\HaltException $e) {
  // Do nothing, just stops execution
  // pre($e);
} catch (\SCDS\CSRFValidityException $e) {
  // Deals with any uncaught CSRF problems
  ob_get_clean();
  halt(403, false);
  // pre($e);
} catch (Exception $e) {
  // This catches any uncaught exceptions.
  ob_get_clean();
  halt(500, false);
  // pre($e);
} catch (Error $e) {
  // This catches any fatal or recoverable errors.
  ob_get_clean();
  halt(500, false);
  // pre($e);
} finally {
  // Any actions which must always happen at end
}

// Close SQL Database Connections
$db = null;
