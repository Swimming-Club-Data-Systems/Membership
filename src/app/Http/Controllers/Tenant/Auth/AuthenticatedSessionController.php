<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\UserLoginTwoFactorCode;
use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use PragmaRX\Google2FA\Google2FA;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     *
     * @param \App\Http\Requests\Auth\LoginRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(LoginRequest $request)
    {
        $user = $request->authenticate();

        $request->session()->put('auth.check_two_factor_code', true);
        $request->session()->put('auth.two_factor_code_user', $user->UserID);
        $request->session()->put('auth.remember', $request->boolean('remember'));

        // Check if this user uses Google Authenticator
        if ($user->getOption('hasGoogleAuth2FA') && $user->getOption('GoogleAuth2FASecret')) {
            // Using Google
        } else {
            // Sending code by email
            $code = random_int(100000, 999999);
            $request->session()->put('auth.two_factor_code', $code);
            Mail::to($user)->send(new UserLoginTwoFactorCode($user, $code));
        }

        return Inertia::location(route('two_factor'));
    }

    /**
     * Display the login view.
     *
     // * @return \Illuminate\Http\Response
     */
    public function check(Request $request)
    {
        if ($request->session()->missing('auth.check_two_factor_code')) {
            return Inertia::location(route('login'));
        }

        return Inertia::render('Auth/TwoFactorChallenge', [
            'isTOTP' => $request->session()->missing('auth.two_factor_code'),
        ]);
    }

    /**
     * Display the login view.
     *
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        $user = User::findOrFail($request->session()->get('auth.two_factor_code_user'));

        // Get or set code
        $code = $request->session()->get('auth.two_factor_code');
        if (!$code) {
            $code = random_int(100000, 999999);
            $request->session()->put('auth.two_factor_code', $code);
        }

        Mail::to($user)->send(new UserLoginTwoFactorCode($user, $code));

        return Inertia::location(route('two_factor'));
    }

    /**
     * Display the login view.
     *
     * @return \Inertia\Response
     */
    public function confirm(Request $request)
    {
        if ($request->session()->missing('auth.check_two_factor_code')) {
            return Inertia::location(route('login'));
        }

        $user = User::findOrFail($request->session()->get('auth.two_factor_code_user'));

        $invalidMessage = "The authentication code provided was invalid";

        // Check
        if ($request->session()->missing('auth.two_factor_code')) {
            // Verify TOTP
            $totp = new Google2FA();
            if (!$totp->verifyKey($user->getOption('GoogleAuth2FASecret'), $request->input('code'))) {
                throw ValidationException::withMessages([
                    'code' => $invalidMessage,
                ]);
            }
        } else {
            if ($request->input('code') != $request->session()->get('auth.two_factor_code')) {
                throw ValidationException::withMessages([
                    'code' => $invalidMessage,
                ]);
            }
        }

        Auth::login($user, (bool)$request->session()->pull('auth.remember'));

        // The user has just logged in with multiple factors so set confirmed at time
        // Otherwise the user is hit with confirm immediately if heading to profile routes.
        $request->session()->put('auth.password_confirmed_at', time());

        $request->session()->forget([
            'auth.two_factor_code_user',
            'auth.two_factor_code',
            'auth.check_two_factor_code'
        ]);

        $request->session()->regenerate();

        $url = $request->session()->get('url.intended') ?? "";

        if (Route::getRoutes()->match(Request::create($url))->getName() == "login.v1") {
            $request->session()->forget('url.intended');
            $controller = new V1LoginController();
            return $controller($request);
        } else {
            return redirect()->intended(RouteServiceProvider::HOME);
        }
    }

    /**
     * Display the login view.
     *
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
        if ($request->session()->has('auth.check_two_factor_code')) {
            return Inertia::location(route('two_factor'));
        }

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return Inertia::location('/v1/logout');
    }

    /**
     * Return information on whether the user has webauthn or an SSO url to use
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkUsername(Request $request)
    {
        // Find user by email
        /**
         * @var User $user
         */
        $user = User::query()->where('EmailAddress', $request->input('email'))->first();

        // Get tenant object
        /**
         * @var Tenant $tenant
         */
        $tenant = tenant();

        // TENANT_ENABLE_STAFF_OAUTH
        // TENANT_OAUTH_EMAIL_DOMAIN

        $hasWebauthn = false;
        $ssoUrl = null;

        if ($user) {
            $hasWebauthn = $user->userCredentials()->count() > 0;
        }

        if ($user && $tenant->getOption('TENANT_ENABLE_STAFF_OAUTH') && Str::endsWith($user->EmailAddress, $tenant->getOption('TENANT_OAUTH_EMAIL_DOMAIN'))) {
            $ssoUrl = url("login/oauth?email=" . urlencode($user->EmailAddress));
        }

        return response()->json([
            "has_webauthn" => $hasWebauthn,
            "sso_url" => $ssoUrl,
        ]);
    }

    public function confirmDestroy() {
        return Inertia::render('Auth/Logout');
    }
}
