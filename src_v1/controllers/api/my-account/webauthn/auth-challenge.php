<?php

use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\PublicKeyCredentialSource;

$userEntityRepository = new WebAuthnImplementation\PublicKeyCredentialUserEntityRepository();
$credentialSourceRepository = new WebAuthnImplementation\PublicKeyCredentialSourceRepository();
$server = WebAuthnImplementation\Server::get();

$post = json_decode(file_get_contents('php://input'));

// UseEntity found using the username.
$userEntity = $userEntityRepository->findWebauthnUserByUsername($post->username);

// Get the list of authenticators associated to the user
$credentialSources = $credentialSourceRepository->findAllForUserEntity($userEntity);

// Convert the Credential Sources into Public Key Credential Descriptors
$allowedCredentials = array_map(function (PublicKeyCredentialSource $credential) {
    return $credential->getPublicKeyCredentialDescriptor();
}, $credentialSources);

// We generate the set of options.
$publicKeyCredentialRequestOptions = $server->generatePublicKeyCredentialRequestOptions(
    PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED, // Default value
    $allowedCredentials
);

$creationJson = json_encode($publicKeyCredentialRequestOptions);

$_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestUsername'] = $post->username;
$_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestOptions'] = $creationJson;

header("content-type: application/json");
echo $creationJson;