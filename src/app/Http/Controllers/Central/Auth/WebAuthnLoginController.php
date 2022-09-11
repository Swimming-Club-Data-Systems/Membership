<?php

namespace App\Http\Controllers\Central\Auth;

use App\Models\Central\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\PublicKeyCredentialSource;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Webauthn\AuthenticationExtensions\AuthenticationExtension;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
use App\Business\CentralWebAuthnImplementation\PublicKeyCredentialUserEntityRepository;
use App\Business\CentralWebAuthnImplementation\PublicKeyCredentialSourceRepository;
use App\Business\CentralWebAuthnImplementation\Server;
use App\Http\Controllers\Controller;
use Webauthn\TokenBinding\IgnoreTokenBindingHandler;

class WebAuthnLoginController extends Controller
{
    public function challenge(Request $request)
    {
        $userEntityRepository = new PublicKeyCredentialUserEntityRepository();
        $credentialSourceRepository = new PublicKeyCredentialSourceRepository();
        // $server = Server::get();

        $allowedCredentials = [];
        if ($request->input('username')) {
            // UseEntity found using the username.
            $userEntity = $userEntityRepository->findWebauthnUserByUsername($request->input('username'));

            // Get the list of authenticators associated to the user
            $credentialSources = $credentialSourceRepository->findAllForUserEntity($userEntity);

            // Convert the Credential Sources into Public Key Credential Descriptors
            $allowedCredentials = array_map(function (PublicKeyCredentialSource $credential) {
                return $credential->getPublicKeyCredentialDescriptor();
            }, $credentialSources);
        }

        // We generate the set of options.
        // $publicKeyCredentialRequestOptions = $server->generatePublicKeyCredentialRequestOptions(
        //     PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED, // Default value
        //     $allowedCredentials,
        // );

        $publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::create(random_bytes(32))->setUserVerification(PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED)->setRpId(tenant('Domain'));

        if (sizeof($allowedCredentials) > 0) {
            $publicKeyCredentialRequestOptions = $publicKeyCredentialRequestOptions->allowCredentials(...$allowedCredentials);
        }

        $publicKeyCredentialRequestOptionsJson = json_encode($publicKeyCredentialRequestOptions);
        $publicKeyCredentialRequestOptions = json_decode($publicKeyCredentialRequestOptionsJson, true);

        if ($request->input('mediation') == "conditional") {
            $publicKeyCredentialRequestOptions['mediation'] = "conditional";
        }

        $request->session()->put('webauthn_credential_request_options', json_encode($publicKeyCredentialRequestOptions));

        return response()->json($publicKeyCredentialRequestOptions);
    }

    public function verify(Request $request)
    {
        $options = $request->session()->pull('webauthn_credential_request_options');

        $publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::createFromString($options);

        // The manager will receive data to load and select the appropriate
        $attestationStatementSupportManager = AttestationStatementSupportManager::create();
        $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());

        $attestationObjectLoader = AttestationObjectLoader::create(
            $attestationStatementSupportManager
        );

        $publicKeyCredentialLoader = PublicKeyCredentialLoader::create(
            $attestationObjectLoader
        );

        $publicKeyCredential = $publicKeyCredentialLoader->loadArray($request->input());

        $authenticatorAssertionResponse = $publicKeyCredential->getResponse();
        if (!$authenticatorAssertionResponse instanceof AuthenticatorAssertionResponse) {
            //e.g. process here with a redirection to the public key login/MFA page.
        }

        // $request = Request::createFromGlobals();

        $publicKeyCredentialSourceRepository = new PublicKeyCredentialSourceRepository();

        $tokenBindingHandler = IgnoreTokenBindingHandler::create();

        $extensionOutputCheckerHandler = ExtensionOutputCheckerHandler::create();

        $authenticatorAssertionResponseValidator = AuthenticatorAssertionResponseValidator::create(
            $publicKeyCredentialSourceRepository,  // The Credential Repository service
            $tokenBindingHandler,                  // The token binding handler
            $extensionOutputCheckerHandler,        // The extension output checker handler
            Server::getAlgorithmManager()          // The COSE Algorithm Manager
        );

        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );

        $serverRequest = $creator->fromGlobals();

        $publicKeyCredentialSource = $authenticatorAssertionResponseValidator->check(
            $publicKeyCredential->getRawId(),
            $authenticatorAssertionResponse,
            $publicKeyCredentialRequestOptions,
            $serverRequest,
//            $userHandle
            null,
            ['testclub.localhost', 'localhost'],
        );

        $userId = $publicKeyCredentialSource->getUserHandle();

        $user = User::find($userId);

        Auth::guard('central')->login($user);

        $request->session()->regenerate();

        // The user has just logged in with multiple factors so set confirmed at time
        // Otherwise the user is hit with confirm immediately if heading to profile routes.
        $request->session()->put('auth.password_confirmed_at', time());

        $redirectUrl = $request->session()->pull('url.intended', RouteServiceProvider::HOME);

        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
        ]);
    }
}