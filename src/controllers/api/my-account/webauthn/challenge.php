<?php

use Cose\Algorithms;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\Server;

$tenant = app()->tenant;
$user = app()->user;

$server = WebAuthnImplementation\Server::get();

$post = json_decode(file_get_contents('php://input'));

$userEntity = new PublicKeyCredentialUserEntity(
  $user->getEmail(),                              // Username
  $user->getId(),                        // ID
  $user->getName()                                // Display name
);

$credentialSourceRepository = new WebAuthnImplementation\PublicKeyCredentialSourceRepository();
/** This avoids multiple registration of the same authenticator with the user account **/
/** You can remove this code if it is a new user **/
// Get the list of authenticators associated to the user
$credentialSources = $credentialSourceRepository->findAllForUserEntity($userEntity);

// Convert the Credential Sources into Public Key Credential Descriptors
$excludeCredentials = array_map(function (PublicKeyCredentialSource $credential) {
  return $credential->getPublicKeyCredentialDescriptor();
}, $credentialSources);
/** End of optional part**/

$publicKeyCredentialCreationOptions = $server->generatePublicKeyCredentialCreationOptions(
  $userEntity,                                                                // The user entity
  PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE, // We will see this option later
  $excludeCredentials,                                                         // Excluded authenticators
  //   Set [] if new user
);

$creationJson = json_encode($publicKeyCredentialCreationOptions);

$_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialCreationOptions'] = $creationJson;
$_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialName'] = $post->passkey_name;

header("content-type: application/json");
echo $creationJson;
