<?php

$db = app()->db;

if (!isset($_SESSION['SCDS-SU-Login2FA']) || isset($_SESSION['SCDS-SuperUser'])) {
  halt(404);
}

$security_status = true;
if ($_POST['SessionSecurity'] != session_id()) {
  $security_status = false;
}

if (!SCDS\CSRF::verify()) {
  $security_status = false;
}

use PragmaRX\Google2FA\Google2FA;

$ga2fa = new Google2FA();

http_response_code(302);

try {

  if (!$security_status) {
    throw new Exception('CSRF Error');
  }

  if ($ga2fa->verifyKey($_SESSION['SCDS-SU-Login2FA']['TwoFactorHash'], $_POST['auth'])) {

    $hash = hash('sha512', time() . $_SESSION['SCDS-SU-Login2FA']['User'] . '-' . random_bytes(128));
    // Make sure no more than 512 chars, regardless of hash type
    $hash = mb_strimwidth($hash, 0, 512);

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
      // } catch (\GeoIp2\Exception\InvalidDatabaseException $e) {
      //   $geo_string = "Location Information Unavailable";
    } catch (\Exception $e) {
      $geo_string = "Location Information Unavailable";
    }

    $insert = $db->prepare("INSERT INTO `superUsersLogins` (`User`, `IPAddress`, `GeoLocation`, `Browser`, `Platform`, `Mobile`, `Hash`, `HashActive`, `Time`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $mobile = 0;

    $browser_details = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);

    $browser = "Unknown";
    if ($browser_details->browser->version != null && $browser_details->browser->name != null) {
      $browser = $browser_details->browser->name . ' ' . $browser_details->browser->version->toString();
    }

    if ($browser_details != null && $browser_details->isType('mobile')) {
      $mobile = 1;
    }

    $remember_me = 0;
    if ($_SESSION['SCDS-SU-Login2FA']['RememberMe']) {
      $remember_me = 1;
    }

    $date = new \DateTime('now', new \DateTimeZone('UTC'));
    $dbDate = $date->format('Y-m-d H:i:s');

    $login_details = [
      $_SESSION['SCDS-SU-Login2FA']['User'],
      getUserIp(),
      $geo_string,
      $browser,
      $browser_details->os->toString(),
      $mobile,
      $hash,
      $remember_me,
      $dbDate
    ];

    $insert->execute($login_details);

    $secure = true;
    if (app('request')->protocol == 'http' && bool(getenv('IS_DEV'))) {
      $secure = false;
    }
    if ($_SESSION['SCDS-SU-Login2FA']['RememberMe']) {
      $cookiePath = '/';
      setcookie(COOKIE_PREFIX . 'SUPERUSER-AutoLogin', $hash, time() + 60 * 60 * 24 * 120, $cookiePath, getenv('MAIN_DOMAIN'), $secure, false);
    }

    $_SESSION['SCDS-SuperUser'] = $_SESSION['SCDS-SU-Login2FA']['User'];
    unset($_SESSION['SCDS-SU-Login2FA']);
    header("location: " . autoUrl('admin'));
  } else {
    header("location: " . autoUrl('admin/login'));
  }
} catch (Exception $e) {

  header("location: " . autoUrl('admin/login'));
}
