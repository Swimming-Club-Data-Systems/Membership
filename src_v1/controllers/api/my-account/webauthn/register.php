<?php

use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\AuthenticatorAttestationResponse;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredentialCreationOptions;

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialCreationOptions'])) {
    throw new Exception("Missing session value");
}

$server = WebAuthnImplementation\Server::get();
$publicKeyCredentialSourceRepository = new WebAuthnImplementation\PublicKeyCredentialSourceRepository();

$publicKeyCredentialCreationOptions = PublicKeyCredentialCreationOptions::createFromString($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialCreationOptions']);

$psr17Factory = new Psr17Factory();
$creator = new ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

$serverRequest = $creator->fromGlobals();

try {
    $publicKeyCredentialSource = $server->loadAndCheckAttestationResponse(
        file_get_contents('php://input'),
        $publicKeyCredentialCreationOptions, // The options you stored during the previous step
        $serverRequest                       // The PSR-7 request
    );

    // The user entity and the public key credential source can now be stored using their repository
    // The Public Key Credential Source repository must implement Webauthn\PublicKeyCredentialSourceRepository
    $publicKeyCredentialSourceRepository->saveCredentialSource($publicKeyCredentialSource);

    // Set the name in db
    $credentialId = $publicKeyCredentialSource->getPublicKeyCredentialId();

    if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialName']) && mb_strlen(trim($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialName'])) > 0) {
        $db = app()->db;
        $update = $db->prepare("UPDATE `userCredentials` SET `credential_name` = ? WHERE `credential_id` = ?");
        $update->execute([
            $_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialName'],
            base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId()),
        ]);
    }

    // If you create a new user account, you should also save the user entity
    // $userEntityRepository->save($userEntity);
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

unset($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialName']);
unset($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialCreationOptions']);
