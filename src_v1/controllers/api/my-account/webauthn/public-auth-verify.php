<?php

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Webauthn\PublicKeyCredentialRequestOptions;

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestOptions'])) {
  throw new Exception("Missing session value");
}

$userEntityRepository = new WebAuthnImplementation\PublicKeyCredentialUserEntityRepository();
$server = WebAuthnImplementation\Server::get();

$psr17Factory = new Psr17Factory();
$creator = new ServerRequestCreator(
  $psr17Factory, // ServerRequestFactory
  $psr17Factory, // UriFactory
  $psr17Factory, // UploadedFileFactory
  $psr17Factory  // StreamFactory
);

$serverRequest = $creator->fromGlobals();

$publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::createFromString($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestOptions']);

$userEntity = $userEntityRepository->findWebauthnUserByUserHandle(base64_decode(json_decode(file_get_contents('php://input'))->response->userHandle));

// reportError(json_decode(file_get_contents('php://input')));
// return;

try {
  $publicKeyCredentialSource = $server->loadAndCheckAssertionResponse(
    file_get_contents('php://input'),
    $publicKeyCredentialRequestOptions, // The options you stored during the previous step
    $userEntity,                        // The user entity
    $serverRequest                      // The PSR-7 request
  );

  $userId = $publicKeyCredentialSource->getUserHandle();

  $_SESSION['TENANT-' . app()->tenant->getId()]['REACT_LOGIN_REMEMBER_ME'] = true;

  $target = autoUrl("");
  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestTargetURL'])) {
    $target = autoUrl(trim($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestTargetURL'], "/"));
  }

  $url = autoUrl("api/auth/login/success-redirect-flow?target=" . urlencode($target));

  $_SESSION['TENANT-' . app()->tenant->getId()]['REACT_LOGIN_USER_CONFIRMED'] = $userId;

  $event = 'UserLogin-WebAuthn';
  AuditLog::new($event, 'Signed in from ' . getUserIp(), $userId);

  //If everything is fine, this means the user has correctly been authenticated using the
  // authenticator defined in $publicKeyCredentialSource
  echo json_encode([
    "success" => true,
    "redirect_url" => $url
  ]);
} catch (\Throwable $exception) {
  // Something went wrong!
  // reportError($exception);
  echo json_encode([
    "success" => false,
    "message" => $exception->getMessage(),
  ]);
}

unset($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestTargetURL']);
unset($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestOptions']);
