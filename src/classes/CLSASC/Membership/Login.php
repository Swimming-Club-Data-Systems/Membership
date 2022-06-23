<?php

namespace CLSASC\Membership;

use DateTime;
use DateTimeZone;

class Login
{
  private $db;
  private $user;
  private $stayLoggedIn;
  private $noUserWarning;
  private $reLogin;

  function __construct($db)
  {
    $this->db = $db;
  }

  public function setUser($user)
  {
    $this->user = $user;
    $checkExists = $this->db->prepare("SELECT COUNT(*) FROM users WHERE UserID = ?");
    $checkExists->execute([$user]);
    if ($checkExists->fetchColumn() == 0) {
      throw new \Exception();
    }
  }

  public function stayLoggedIn($option = true)
  {
    $this->stayLoggedIn = $option;
  }

  public function reLogin($option = true)
  {
    $this->reLogin = $option;
  }

  public function preventWarningEmail($option = true)
  {
    $this->noUserWarning = $option;
  }

  public function login()
  {
    $getUserDetails = $this->db->prepare("SELECT EmailAddress, Forename, Surname, UserID FROM users WHERE UserID = ?");
    $getUserDetails->execute([$this->user]);
    $details = $getUserDetails->fetch(\PDO::FETCH_ASSOC);

    $_SESSION['TENANT-' . app()->tenant->getId()]['EmailAddress'] = $details['EmailAddress'];
    $_SESSION['TENANT-' . app()->tenant->getId()]['Forename'] = $details['Forename'];
    $_SESSION['TENANT-' . app()->tenant->getId()]['Surname'] = $details['Surname'];
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'] = $details['UserID'];
    $_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'] = 1;

    $currentUser = new \User($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], true);

    $hash = hash('sha512', time() . $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'] . random_bytes(64));

    $geo_string = "Location Information Unavailable";

    try {
      $reader = new \GeoIp2\Database\Reader(BASE_PATH . 'storage/geoip/GeoLite2-City.mmdb');
      $record = $reader->city(getUserIp());
      $city = '';
      if ($record->city->name != "") {
        $city = $record->city->name . ', ';
      }
      $subdivision = '';
      if ($record->mostSpecificSubdivision->name != "" && $record->mostSpecificSubdivision->name != $record->city->name) {
        $subdivision = $record->mostSpecificSubdivision->name . ', ';
      }
      $country = '';
      if ($record->country->name != "") {
        $country = $record->country->name;
      }

      $geo_string = $city . $subdivision . $country;
    } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
      $geo_string = "Unknown Location";
    } catch (\GeoIp2\Exception\InvalidDatabaseException $e) {
      $geo_string = "Location Information Unavailable";
    } catch (\Exception $e) {
      $geo_string = "Location Information Unavailable";
    }

    $sql = "INSERT INTO `userLogins` (`UserID`, `IPAddress`, `GeoLocation`, `Browser`, `Platform`, `Mobile`, `Hash`, `HashActive`, `Time`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $mobile = 0;

    $browser_details = new \WhichBrowser\Parser(getallheaders());

    $browser = "Unknown";
    if ($browser_details->browser->version != null && $browser_details->browser->name != null) {
      $browser = $browser_details->browser->name . ' ' . $browser_details->browser->version->toString();
    }

    if ($browser_details != null && $browser_details->isType('mobile')) {
      $mobile = 1;
    }

    $remember_me = 0;
    if ($this->stayLoggedIn) {
      $remember_me = 1;
    }

    $date = new \DateTime('now', new \DateTimeZone('UTC'));
    $dbDate = $date->format('Y-m-d H:i:s');

    $login_details = [
      $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
      getUserIp(),
      $geo_string,
      $browser,
      $browser_details->os->toString(),
      $mobile,
      $hash,
      $remember_me,
      $dbDate
    ];

    if (!$this->reLogin) {
      try {
        $query = $this->db->prepare($sql);
        $query->execute($login_details);
      } catch (\Exception $e) {
        halt(500);
      }
    }

    $user_info_cookie = json_encode([
      'Forename' => $details['Forename'],
      'Surname' => $details['Surname'],
      'Account' => $details['UserID'],
      'TopUAL'  => null
    ]);

    if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoginSec'])) {
      unset($_SESSION['TENANT-' . app()->tenant->getId()]['LoginSec']);
    }

    $secure = true;
    if (app('request')->protocol == 'http' && bool(getenv('IS_DEV'))) {
      $secure = false;
    }
    if (!$this->reLogin) {
      $cookiePath = '/' . app()->tenant->getCodeId();
      if (getenv('MAIN_DOMAIN')) {
        $cookiePath = '/';
      }
      setcookie(COOKIE_PREFIX . 'TENANT-' . app()->tenant->getId() . '-' . 'AutoLogin', $hash, time() + 60 * 60 * 24 * 120, $cookiePath, app('request')->hostname, $secure, false);
    }

    // Test if we've seen a login from here before
    $login_before_data = [
      $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
      getUserIp(),
      ucwords(app('request')->browser()),
      ucwords(app('request')->platform())
    ];

    $login_before = $this->db->prepare("SELECT COUNT(*) FROM `userLogins` WHERE `UserID` = ? AND `IPAddress` = ? AND `Browser` = ? AND `Platform` = ?");
    $login_before->execute($login_before_data);
    $login_before_count = $login_before->fetchColumn();

    if ($login_before_count == 1 && !$this->noUserWarning && !$this->reLogin) {

      $subject = "New Account Login";
      $message = '<p>Somebody just logged into your ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Account from ' . $browser . ', using a device running ' . $browser_details->os->toString() . ' we believe was located in ' . $geo_string . '*.</p><p>We haven\'t seen a login from this location and device before.</p><p>If this was you then you can ignore this email. If this was not you, please <a href="' . autoUrl("") . '">log in to your account</a> and <a href="' . autoUrl("myaccount/password") . '">change your password</a> as soon as possible.</p><p>Kind Regards, <br>The ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Team</p><p class="text-muted small">* We\'ve estimated your location from your public IP Address. The location given may not be where you live.</p>';
      $notify = "INSERT INTO notify (`UserID`, `Status`, `Subject`, `Message`,
      `ForceSend`, `EmailType`) VALUES (?, 'Queued', ?, ?, 0, 'Security')";
      try {
        $this->db->prepare($notify)->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $subject, $message]);
      } catch (\Exception $e) {
        halt(500);
      }
    }

    return $currentUser;
  }
}
