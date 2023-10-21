<?php

namespace App\Http\Controllers\Central\Auth;

use App\Business\Helpers\OAuthLogin;
use App\Http\Controllers\Controller;
use App\Models\Central\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Microsoft\Graph\Graph;

class AADOAuthController extends Controller
{
    public function start(Request $request): \Illuminate\Http\RedirectResponse
    {
        if ($request->get('target_host')) {
            $request->session()->put('target_host', $request->get('target_host'));
        }

        $provider = OAuthLogin::getMultiTenantProvider();

        $options = [];

        if ($request->get('email')) {
            $options['login_hint'] = $request->input('email');
        }

        if ($request->get('domain_hint')) {
            $options['domain_hint'] = $request->get('domain_hint');
        }

        $authorizationUrl = $provider->getAuthorizationUrl($options);

        return redirect($authorizationUrl);
    }

    public function verify(Request $request)
    {
        $provider = OAuthLogin::getMultiTenantProvider();

        $host = $request->session()->pull('target_host');

        if (! $request->get('code')) {
            // No code returned, return to start page
            if ($host) {
                // Go to tenant login route
                return 'https://'.$host.Redirect::route('login');
            } else {
                // Go to central login route
                return Redirect::route('login');
            }
        }

        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $request->get('code'),
        ]);

        $graph = new Graph();
        $graph->setAccessToken($accessToken->getToken());

        /** @var \Microsoft\Graph\Model\UserIdentity $identity */
        $identity = $graph->createRequest('GET', 'https://graph.microsoft.com/oidc/userinfo')
            ->setReturnType(\Microsoft\Graph\Model\UserIdentity::class)
            ->execute();

        // /me?$select=displayName,userPrincipalName,mail
        /** @var \Microsoft\Graph\Model\User $idpUser */
        $idpUser = $graph->createRequest('GET', '/me')
            ->setReturnType(\Microsoft\Graph\Model\User::class)
            ->execute();

        //        return [$identity, $idpUser];

        if ($host) {
            // Send the user to the tenant they came from with encrypted details
            $path = URL::route('login.signed_email', [
                'data' => Crypt::encrypt([
                    'email' => $idpUser->getMail(),
                    //                'tenantId' => Crypt::encryptString(1),
                    'expire' => now()->addMinute(),
                ]),
            ], false);

            $url = 'https://'.$host.$path;

            return Redirect::away($url);
        } else {
            // Look for a user in tenant administration and log them in
            /**
             * @var User $user
             */
            $user = User::query()->where('email', $idpUser->getMail())->first();

            if (! $user) {
                return Redirect::route(RouteServiceProvider::HOME);
            }

            Auth::guard('central')->login($user, (bool) $request->session()->pull('auth.remember'));

            // The user has just logged in with SSO so set confirmed at time
            // Otherwise the user is hit with confirm immediately if heading to profile routes.
            $request->session()->put('auth.password_confirmed_at', time());

            $request->session()->regenerate();

            return redirect()->intended(RouteServiceProvider::HOME);
        }
    }
}
