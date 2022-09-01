<?php

namespace App\Http\Controllers\Central\Auth;

use App\Business\Helpers\OAuthLogin;
use App\Models\Central\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OAuthLoginController extends Controller
{
    public function start(Request $request): \Illuminate\Http\RedirectResponse
    {
        $provider = OAuthLogin::getCentralProvider();

        $options = [];

        if ($request->get('email')) {
            $options['login_hint'] = $request->input('email');
        }

        $authorizationUrl = $provider->getAuthorizationUrl($options);

        return redirect($authorizationUrl);
    }

    public function verify(Request $request)
    {
        $provider = OAuthLogin::getCentralProvider();

        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $request->get('code'),
        ]);

        $graph = new Graph();
        $graph->setAccessToken($accessToken->getToken());

        $idpUser = $graph->createRequest('GET', '/me?$select=displayName,userPrincipalName,mail')
            ->setReturnType(Model\User::class)
            ->execute();

        /**
         * @var User $user
         */
        $user = User::query()->where('email', $idpUser->getMail())->first();

        if (!$user) {
            abort(404);
        }

        Auth::guard('central')->login($user, (bool)$request->session()->pull('auth.remember'));

        // The user has just logged in with SSO so set confirmed at time
        // Otherwise the user is hit with confirm immediately if heading to profile routes.
        $request->session()->put('auth.password_confirmed_at', time());

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
