<?php

namespace App\Http\Controllers\Central\Auth;

use App\Http\Controllers\Controller;
use App\Models\Central\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     *
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
        return Inertia::render('CentralAuth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.

        /**
         * @var User $user
         */
        $user = User::query()->where('email', $request->input('email'))->first();

        if ($user) {
            $token = Password::createToken($user);
            $user->sendPasswordResetNotification($token);
        }

//        $status = Password::sendResetLink(
//            $request->only('email')
//        );

        if ($user) {
            return back()->with('status', __(Password::RESET_LINK_SENT));
        }

        throw ValidationException::withMessages([
            'email' => [trans(Password::INVALID_USER)],
        ]);
    }

    protected function guard()
    {
        return Auth::guard('central');
    }
}
