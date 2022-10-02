<?php

namespace App\Http\Controllers\Central;

use App\Business\CentralWebAuthnImplementation\PublicKeyCredentialSourceRepository;
use App\Business\CentralWebAuthnImplementation\PublicKeyCredentialUserEntityRepository;
use App\Business\CentralWebAuthnImplementation\Server;
use App\Http\Controllers\Controller;
use App\Models\Central\Auth\UserCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use ParagonIE\ConstantTime\Base64UrlSafe;
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
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function challenge(Request $request)
    {
        $userEntityRepository = new PublicKeyCredentialUserEntityRepository();
        $credentialSourceRepository = new PublicKeyCredentialSourceRepository();

        // UseEntity found using the username.
        // $userEntity = $userEntityRepository->findWebauthnUserByUsername($request->input('username'));
        $userEntity = $userEntityRepository->findWebauthnUserByUserHandle(Auth::id());

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

        PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_DIRECT;
        $publicKeyCredentialCreationOptions = PublicKeyCredentialCreationOptions::create(
            $rpEntity,
            $userEntity,
            $challenge,
            Server::getPublicKeyParameterList(),
        )
            ->excludeCredentials(...$allowedCredentials)
            ->setAuthenticatorSelection(AuthenticatorSelectionCriteria::create())
            ->setAttestation(PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE);

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

        $publicKeyCredential = null;

        try {
            $publicKeyCredential = $publicKeyCredentialLoader->loadArray($request->input());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Passkey could not be loaded. Please try again.',
            ]);
        }

        $authenticatorAttestationResponse = $publicKeyCredential->getResponse();
        if (!$authenticatorAttestationResponse instanceof AuthenticatorAttestationResponse) {
            // Failed, report back
            return response()->json([
                'success' => false,
                'message' => 'Passkey verification failed. Please try again.',
            ]);
        }

        try {

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
            $credential = UserCredential::where('credential_id', Base64UrlSafe::encodeUnpadded($publicKeyCredentialSource->getPublicKeyCredentialId()))->first();
            if ($credential) {
                $credential->credential_name = $request->session()->pull('webauthn_credential_registration_name');
                $credential->save();
            }

            $request->session()->forget('webauthn_credential_registration_request_options');

            $request->session()->flash('flash_bag.manage_passkeys.success', 'We have saved your new passkey (' . $credential->credential_name . ').');

            return response()->json([
                'success' => true,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Passkey verification failed. Please try again.',
            ]);
        }
    }

    public function delete(Request $request, UserCredential $credential)
    {
        $this->authorize('delete', $credential);

        $credential->delete();

        $request->session()->flash('flash_bag.delete_credential.success', 'We have deleted ' . $credential->credential_name . ' from your list of passkeys.');

        return Redirect::route('my_account.security');
    }
}
