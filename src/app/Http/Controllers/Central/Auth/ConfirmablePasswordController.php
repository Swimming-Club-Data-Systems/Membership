<?php

namespace App\Http\Controllers\Central\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

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

        $ssoUrl = null;

        return Inertia::render('CentralAuth/ConfirmPassword', [
            'has_webauthn' => $hasCredentials,
            'sso_url' => $ssoUrl,
        ]);
    }

    /**
     * Confirm the user's password.
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        if (! Auth::guard('central')->validate([
            'email' => $request->user('central')->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
