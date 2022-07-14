<?php

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Webauthn\PublicKeyCredentialRequestOptions;

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestOptions']) || !isset($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestUsername'])) {
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

$userEntity = $userEntityRepository->findWebauthnUserByUsername($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestUsername']);

try {
  $publicKeyCredentialSource = $server->loadAndCheckAssertionResponse(
    file_get_contents('php://input'),
    $publicKeyCredentialRequestOptions, // The options you stored during the previous step
    $userEntity,                        // The user entity
    $serverRequest                      // The PSR-7 request
  );

  //If everything is fine, this means the user has correctly been authenticated using the
  // authenticator defined in $publicKeyCredentialSource
  echo json_encode([
    "success" => true,
  ]);
} catch (\Throwable $exception) {
  // Something went wrong!
  reportError($exception);
  echo json_encode([
    "success" => false,
    "message" => $exception->getMessage(),
  ]);
}

unset($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestUsername']);
unset($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestOptions']);
