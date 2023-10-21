<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Business\Helpers\OAuthLogin;
use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class OAuthLoginController extends Controller
{
    public function start(Request $request): \Illuminate\Http\RedirectResponse
    {
        $provider = OAuthLogin::getProvider();

        $options = [];

        if ($request->get('email')) {
            $options['login_hint'] = $request->input('email');
        }

        $authorizationUrl = $provider->getAuthorizationUrl($options);

        return redirect($authorizationUrl);
    }

    public function verify(Request $request)
    {
        $provider = OAuthLogin::getProvider();

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
        $user = User::query()->where('EmailAddress', $idpUser->getMail())->first();

        if (! $user) {
            abort(404);
        }

        Auth::login($user, (bool) $request->session()->pull('auth.remember'));

        // The user has just logged in with SSO so set confirmed at time
        // Otherwise the user is hit with confirm immediately if heading to profile routes.
        $request->session()->put('auth.password_confirmed_at', time());

        $request->session()->regenerate();

        $url = $request->session()->get('url.intended') ?? '';

        if (Route::getRoutes()->match(Request::create($url))->getName() == 'login.v1') {
            $request->session()->forget('url.intended');
            $controller = new V1LoginController();

            return $controller($request);
        } else {
            return redirect()->intended(RouteServiceProvider::HOME);
        }
    }

    public function signedEmail(Request $request)
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $encryptedData = $request->get('data');
        $data = Crypt::decrypt($encryptedData);

        if (date(Arr::get($data, 'expire')) < now()) {
            abort(403, 'Token expired, please sign in again.');
        }

        //        $tenantId = Arr::get($data, 'tenantId');
        //
        //        if ($tenant->ID != $tenantId) {
        //            abort(403, 'Invalid tenant identifier.');
        //        }

        $email = Arr::get($data, 'email');

        /**
         * @var User $user
         */
        $user = User::where('EmailAddress', $email)->first();

        if (! $user) {
            abort(404);
        }

        // Remember is always false as you got here via an IDP
        Auth::login($user, false);

        // The user has just logged in with SSO so set confirmed at time
        // Otherwise the user is hit with confirm immediately if heading to profile routes.
        $request->session()->put('auth.password_confirmed_at', time());

        $request->session()->regenerate();

        $url = $request->session()->get('url.intended') ?? '';

        if (Route::getRoutes()->match(Request::create($url))->getName() == 'login.v1') {
            $request->session()->forget('url.intended');
            $controller = new V1LoginController();

            return $controller($request);
        } else {
            return redirect()->intended(RouteServiceProvider::HOME);
        }
    }
}
