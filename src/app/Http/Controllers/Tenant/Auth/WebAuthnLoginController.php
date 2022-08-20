<?php

namespace App\Http\Controllers\Tenant\Auth;

use Illuminate\Http\Request;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\PublicKeyCredentialSource;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Webauthn\AuthenticationExtensions\AuthenticationExtension;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
use App\Business\WebAuthnImplementation\PublicKeyCredentialUserEntityRepository;
use App\Business\WebAuthnImplementation\PublicKeyCredentialSourceRepository;
use App\Business\WebAuthnImplementation\Server;
use App\Http\Controllers\Controller;

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

        // IF !$options THROW AN ERROR

        $userEntityRepository = new PublicKeyCredentialUserEntityRepository();
        $server = Server::get();

        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );

        $serverRequest = $creator->fromGlobals();

        $publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::createFromString($options);

        $userEntity = $userEntityRepository->findWebauthnUserByUserHandle(base64_decode($request->input('response.userHandle')));

        unset($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestTargetURL']);
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['WebAuthnCredentialRequestOptions']);

        try {
            $publicKeyCredentialSource = $server->loadAndCheckAssertionResponse(
                file_get_contents('php://input'),
                $publicKeyCredentialRequestOptions, // The options you stored during the previous step
                $userEntity,                        // The user entity
                $serverRequest                      // The PSR-7 request
            );

            $userId = $publicKeyCredentialSource->getUserHandle();

            // Deal with loging them in

            return response()->json([]);

            //If everything is fine, this means the user has correctly been authenticated using the
            // authenticator defined in $publicKeyCredentialSource
            // echo json_encode([
            //     "success" => true,
            //     "redirect_url" => $url
            // ]);
        } catch (\Throwable $exception) {
            // Something went wrong!
            // reportError($exception);
            return response()->json([
                "success" => false,
                "message" => $exception->getMessage(),
            ]);
        }
    }
}
