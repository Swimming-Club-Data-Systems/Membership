<?php

namespace App\Http\Controllers\Central\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CentralLoginRequest;
use App\Mail\CentralUserLoginTwoFactorCode;
use App\Models\Central\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use PragmaRX\Google2FA\Google2FA;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('CentralAuth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param \App\Http\Requests\Auth\LoginRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CentralLoginRequest $request)
    {
        // $result = Auth::guard('central')->attempt($request->only('email', 'password'), $request->boolean('remember'));

        /** @var User $user */
        $user = $request->authenticate();

        $request->session()->put('auth.check_two_factor_code', true);
        $request->session()->put('auth.two_factor_code_user', $user->id);
        $request->session()->put('auth.remember', $request->boolean('remember'));

        // Sending code by email
        $code = random_int(100000, 999999);
        $request->session()->put('auth.two_factor_code', $code);
        Mail::to($user)->send(new CentralUserLoginTwoFactorCode($user, $code));

        return Inertia::location(route('central.two_factor'));
    }

    /**
     * Display the login view.
     *
    // * @return \Illuminate\Http\Response
     */
    public function check(Request $request)
    {
        if ($request->session()->missing('auth.check_two_factor_code')) {
            return Inertia::location(route('central.login'));
        }

        return Inertia::render('CentralAuth/TwoFactorChallenge', [
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

        Mail::to($user)->send(new CentralUserLoginTwoFactorCode($user, $code));

        return Inertia::location(route('central.two_factor'));
    }

    /**
     * Display the login view.
     *
     * @return \Inertia\Response
     */
    public function confirm(Request $request)
    {
        if ($request->session()->missing('auth.check_two_factor_code')) {
            return Inertia::location(route('central.login'));
        }

        $user = User::findOrFail($request->session()->get('auth.two_factor_code_user'));

        $invalidMessage = "The authentication code provided was invalid";

        // Check
        if ($request->session()->missing('auth.two_factor_code')) {
            // Verify TOTP
            $totp = new Google2FA();
            if (!$totp->verifyKey($user->getOption('GoogleAuth2FASecret'), $response->input('code'))) {
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

        Auth::guard('central')->login($user, (bool)$request->session()->pull('auth.remember'));

        // The user has just logged in with multiple factors so set confirmed at time
        // Otherwise the user is hit with confirm immediately if heading to profile routes.
        $request->session()->put('auth.password_confirmed_at', time());

        $request->session()->forget([
            'auth.two_factor_code_user',
            'auth.two_factor_code',
            'auth.check_two_factor_code'
        ]);

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('central')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
