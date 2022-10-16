<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Business\Helpers\OAuthLogin;
use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     *
     * @return \Inertia\Response
     */
    public function show()
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        // Does this user have webauthn credentials?
        $hasCredentials = $user->userCredentials()->count() > 0;

        /**
         * @var Tenant $tenant
         */
        $tenant = tenant();

        $ssoUrl = null;

        if ($tenant->getOption('TENANT_ENABLE_STAFF_OAUTH') && Str::endsWith($user->EmailAddress, $tenant->getOption('TENANT_OAUTH_EMAIL_DOMAIN'))) {
            $ssoUrl = url("login/oauth?email=" . urlencode($user->EmailAddress));
        }

        return Inertia::render('Auth/ConfirmPassword', [
            'has_webauthn' => $hasCredentials,
            'sso_url' => $ssoUrl,
        ]);
    }

    /**
     * Confirm the user's password.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        if (!Auth::guard('web')->validate([
            'EmailAddress' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function oauth(Request $request)
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        $provider = OAuthLogin::getProvider();

        $options = [
            'login_hint' => $user->EmailAddress,
        ];

        $authorizationUrl = $provider->getAuthorizationUrl($options);

        return response()->redirectTo($authorizationUrl);
    }

    public function verifyOauth(Request $request)
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

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
         * @var User $reauthenticatedUser
         */
        $reauthenticatedUser = User::query()->where('EmailAddress', $idpUser->getMail())->first();

        if (!$reauthenticatedUser) {
            abort(404);
        }

        if ($reauthenticatedUser->UserID != $user->UserID) {
            $request->session()->flash('error', 'You did not sign in with the same user. Please try again.');
            return redirect(route('password.confirm'));
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
