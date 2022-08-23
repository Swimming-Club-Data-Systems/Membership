<?php

namespace App\Http\Controllers\Tenant;

use App\Business\WebAuthnImplementation\PublicKeyCredentialSourceRepository;
use App\Business\WebAuthnImplementation\PublicKeyCredentialUserEntityRepository;
use App\Business\WebAuthnImplementation\Server;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Auth\UserCredential;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TokenBinding\IgnoreTokenBindingHandler;

class WebauthnRegistrationController extends Controller
{
    public function challenge(Request $request)
    {
        $userEntityRepository = new PublicKeyCredentialUserEntityRepository();
        $credentialSourceRepository = new PublicKeyCredentialSourceRepository();
//        $server = WebAuthnImplementation\Server::get();

//        $post = json_decode(file_get_contents('php://input'));

// UseEntity found using the username.
        $userEntity = $userEntityRepository->findWebauthnUserByUsername($request->input('username'));

        $challenge = random_bytes(16);

// Get the list of authenticators associated to the user
        $credentialSources = $credentialSourceRepository->findAllForUserEntity($userEntity);

// Convert the Credential Sources into Public Key Credential Descriptors
        $allowedCredentials = array_map(function (PublicKeyCredentialSource $credential) {
            return $credential->getPublicKeyCredentialDescriptor();
        }, $credentialSources);

        $rpEntity = Server::getRpEntity();

        $authenticatorSelectionCriteria = AuthenticatorSelectionCriteria::create()
            ->setUserVerification(AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_DISCOURAGED);

// We generate the set of options.
        PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_DIRECT;
        $publicKeyCredentialCreationOptions = PublicKeyCredentialCreationOptions::create(
            $rpEntity,
            $userEntity,
            $challenge,
            Server::getPublicKeyParameterList(),
        )
            ->excludeCredentials(...$allowedCredentials)
            ->setAuthenticatorSelection(AuthenticatorSelectionCriteria::create());

//        $publicKeyCredentialRequestOptions = $server->generatePublicKeyCredentialRequestOptions(
//            PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED, // Default value
//            $allowedCredentials
//        );

//        $creationJson = json_encode($publicKeyCredentialRequestOptions);

//        $_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestUsername'] = $post->username;
//        $_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestOptions'] = $creationJson;

//        header("content-type: application/json");
//        echo $creationJson;

        $request->session()->put('webauthn_credential_registration_request_options',
            json_encode($publicKeyCredentialCreationOptions));
        $request->session()->put('webauthn_credential_registration_name',
            $request->input('passkey_name'));

        return response()->json($publicKeyCredentialCreationOptions);
    }

    public function verify(Request $request)
    {
        if (!$request->session()->exists('webauthn_credential_registration_request_options')) {
            throw ValidationException::withMessages(["No request object in memory."]);
        }

        $options = $request->session()->get('webauthn_credential_registration_request_options');

        $publicKeyCredentialCreationOptions = PublicKeyCredentialCreationOptions::createFromString($options);

        // The manager will receive data to load and select the appropriate
        $attestationStatementSupportManager = AttestationStatementSupportManager::create();
        $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());

        $attestationObjectLoader = AttestationObjectLoader::create(
            $attestationStatementSupportManager
        );

        $publicKeyCredentialLoader = PublicKeyCredentialLoader::create(
            $attestationObjectLoader
        );
//        $publicKeyCredential = $publicKeyCredentialLoader->load($request->input());
        $publicKeyCredential = $publicKeyCredentialLoader->loadArray($request->input());

        $authenticatorAttestationResponse = $publicKeyCredential->getResponse();
        if (!$authenticatorAttestationResponse instanceof AuthenticatorAttestationResponse) {
            // Failed, report back
        }

        $userEntityRepository = new PublicKeyCredentialUserEntityRepository();

        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );

        $serverRequest = $creator->fromGlobals();

        $publicKeyCredentialSourceRepository = new PublicKeyCredentialSourceRepository();

        $tokenBindingHandler = IgnoreTokenBindingHandler::create();

        $extensionOutputCheckerHandler = ExtensionOutputCheckerHandler::create();

        $authenticatorAttestationResponseValidator = AuthenticatorAttestationResponseValidator::create(
            $attestationStatementSupportManager,
            $publicKeyCredentialSourceRepository,
            $tokenBindingHandler,
            $extensionOutputCheckerHandler
        );

        $publicKeyCredentialSource = $authenticatorAttestationResponseValidator->check(
            $authenticatorAttestationResponse,
            $publicKeyCredentialCreationOptions,
            $serverRequest,
            ['testclub.localhost', 'localhost'],
        );

        // Store the key
        $publicKeyCredentialSourceRepository->saveCredentialSource($publicKeyCredentialSource);

        // Set the credential_name
        $credential = UserCredential::where('credential_id', base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId()))->first();
        if ($credential) {
            $credential->credential_name = $request->session()->pull('webauthn_credential_registration_name');
            $credential->save();
        }

        $request->session()->forget('webauthn_credential_registration_request_options');

        return response()->json($publicKeyCredentialSource);

    }
}
