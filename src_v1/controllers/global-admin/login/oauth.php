<?php

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

if (!getenv('ADMIN_OAUTH_CLIENT_ID')) {
  halt(404);
}

$db = app()->db;

$provider = new \League\OAuth2\Client\Provider\GenericProvider([
  'clientId'                => getenv('ADMIN_OAUTH_CLIENT_ID'),    // The client ID assigned to you by the provider
  'clientSecret'            => getenv('ADMIN_OAUTH_CLIENT_SECRET'),    // The client password assigned to you by the provider
  'redirectUri'             => autoUrl('admin/login/oauth'),
  'urlAuthorize'            => getenv('ADMIN_OAUTH_URL_AUTHORIZE'),
  'urlAccessToken'          => getenv('ADMIN_OAUTH_URL_ACCESS_TOKEN'),
  'urlResourceOwnerDetails' => '',
  'scopes'                  => 'openid profile offline_access user.read'
]);

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {

  // Fetch the authorization URL from the provider; this returns the
  // urlAuthorize option and generates and applies any necessary parameters
  // (e.g. state).
  $authorizationUrl = $provider->getAuthorizationUrl();

  // Get the state generated for you and store it to the session.
  $_SESSION['oauth2state'] = $provider->getState();

  // Redirect the user to the authorization URL.
  header('Location: ' . $authorizationUrl);
  exit;

  // Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {

  if (isset($_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
  }

  exit('Invalid state');
} else {

  try {

    // Try to get an access token using the authorization code grant.
    $accessToken = $provider->getAccessToken('authorization_code', [
      'code' => $_GET['code']
    ]);

    // We have an access token, which we may use in authenticated
    // requests against the service provider's API.
    // echo 'Access Token: ' . $accessToken->getToken() . "<br>";
    // echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
    // echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
    // echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";

    try {
      $graph = new Graph();
      $graph->setAccessToken($accessToken->getToken());

      $user = $graph->createRequest('GET', '/me?$select=displayName,userPrincipalName,mail')
        ->setReturnType(Model\User::class)
        ->execute();

      // pre('User:' . $user->getDisplayName() . ', Token:' . $accessToken->getToken());
      // pre('UPN:' . $user->getUserPrincipalName() . ', Main:' . $user->getMail());

      // $user->getMail()

      // See if user exists
      $getUser = $db->prepare("SELECT `ID`, `First`, `Last`, `Email` FROM superUsers WHERE Email = ?");
      $getUser->execute([
        $user->getMail()
      ]);

      $userDetails = $getUser->fetch(PDO::FETCH_ASSOC);

      if (!$userDetails) {
        halt(404);
      }

      $hash = hash('sha512', time() . $userDetails['ID'] . '-' . random_bytes(128));
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
    if (true) {
      $remember_me = 1;
    }

    $date = new \DateTime('now', new \DateTimeZone('UTC'));
    $dbDate = $date->format('Y-m-d H:i:s');

    $login_details = [
      $userDetails['ID'],
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

    $_SESSION['SCDS-SuperUser'] = $userDetails['ID'];
    unset($_SESSION['SCDS-SU-Login2FA']);
    header("location: " . autoUrl('admin'));

      // pre($user);

    } catch (Exception $e) {
      pre($e);
    }

    // The provider provides a way to get an authenticated API request for
    // the service, using the access token; it returns an object conforming
    // to Psr\Http\Message\RequestInterface.
    // $request = $provider->getAuthenticatedRequest(
    //     'GET',
    //     'https://service.example.com/resource',
    //     $accessToken
    // );

  } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

    // Failed to get the access token or user details.
    exit($e->getMessage());
  }
}
