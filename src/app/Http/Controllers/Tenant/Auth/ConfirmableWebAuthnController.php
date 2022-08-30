<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Business\WebAuthnImplementation\PublicKeyCredentialSourceRepository;
use App\Business\WebAuthnImplementation\PublicKeyCredentialUserEntityRepository;
use App\Business\WebAuthnImplementation\Server;
use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TokenBinding\IgnoreTokenBindingHandler;

class ConfirmableWebAuthnController extends Controller
{
    public function challenge(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $userEntityRepository = new PublicKeyCredentialUserEntityRepository();
        $credentialSourceRepository = new PublicKeyCredentialSourceRepository();

        // UseEntity found using the username.
        $userEntity = $userEntityRepository->findWebauthnUserByUserHandle($user->UserID);

        // Get the list of authenticators associated to the user
        $credentialSources = $credentialSourceRepository->findAllForUserEntity($userEntity);

        // Convert the Credential Sources into Public Key Credential Descriptors
        $allowedCredentials = array_map(function (PublicKeyCredentialSource $credential) {
            return $credential->getPublicKeyCredentialDescriptor();
        }, $credentialSources);

        $publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::create(random_bytes(32))
            ->setUserVerification(PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED)
            ->setRpId(tenant('Domain'))
            ->allowCredentials(...$allowedCredentials);

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
        /** @var User $user */
        $user = Auth::user();

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

        if ($userId != $user->UserID) {
            abort(404);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        $url = $request->session()->get('url.intended') ?? "";

        $redirectUrl = RouteServiceProvider::HOME;

        if (Route::getRoutes()->match(Request::create($url))->getName() == "login.v1") {
            $request->session()->forget('url.intended');
            $redirectUrl = V1LoginController::getUrl($user);
        } else {
            $redirectUrl = $request->session()->pull('url.intended', RouteServiceProvider::HOME);
        }

        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
        ]);
    }
}
