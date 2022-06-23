<?php

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

$db = app()->db;
$tenant = app()->tenant;

if (!$tenant->getKey('TENANT_ENABLE_STAFF_OAUTH')) {
  halt(404);
}

$resetFailedLoginCount = $db->prepare("UPDATE users SET WrongPassCount = 0 WHERE UserID = ?");

$provider = new \League\OAuth2\Client\Provider\GenericProvider([
  'clientId'                => $tenant->getKey('TENANT_OAUTH_CLIENT_ID'),    // The client ID assigned to you by the provider
  'clientSecret'            => $tenant->getKey('TENANT_OAUTH_CLIENT_SECRET'),    // The client password assigned to you by the provider
  'redirectUri'             => autoUrl('login/oauth'),
  'urlAuthorize'            => $tenant->getKey('TENANT_OAUTH_URL_AUTHORIZE'),
  'urlAccessToken'          => $tenant->getKey('TENANT_OAUTH_URL_ACCESS_TOKEN'),
  'urlResourceOwnerDetails' => '',
  'scopes'                  => 'openid profile offline_access user.read'
]);

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {

  // Fetch the authorization URL from the provider; this returns the
  // urlAuthorize option and generates and applies any necessary parameters
  // (e.g. state).
  $options = [];

  if (isset($_GET['email'])) {
    $options["login_hint"] = $_GET['email'];
  }

  $authorizationUrl = $provider->getAuthorizationUrl($options);

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
      $getUser = $db->prepare("SELECT `UserID`, `Forename`, `Surname`, `EmailAddress` FROM users WHERE Tenant = ? AND EmailAddress = ?");
      $getUser->execute([
        $tenant->getId(),
        $user->getMail()
      ]);

      $userDetails = $getUser->fetch(PDO::FETCH_ASSOC);

      if (!$userDetails) {
        halt(404);
      }

      try {
        $login = new \CLSASC\Membership\Login($db);
        $login->setUser($userDetails['UserID']);
        // if (isset($_POST['RememberMe']) && bool($_POST['RememberMe'])) {
        //   $login->stayLoggedIn();
        // }
        // $currentUser = app()->user;
        $currentUser = $login->login();
        $resetFailedLoginCount->execute([$userDetails['UserID']]);

        $event = 'UserLogin-IDP-OAUTH';
        // if ($auth_via_google_authenticator) {
        //   $event = 'UserLogin-2FA-App';
        // }
        AuditLog::new($event, 'Signed in from ' . getUserIp(), $currentUser->getId());

        header('Location: ' . autoUrl(''));
      } catch (Exception $e) {
        throw $e;
      }
    } catch (Exception $e) {
      reportError($e);
      halt(403);
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
