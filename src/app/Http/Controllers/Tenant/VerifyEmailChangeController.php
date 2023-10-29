<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailChangeController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('password.confirm');
    }

    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id, $newEmail)
    {
        if (Auth::id() != $id) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        /**
         * @var User $user
         */
        $user = Auth::user();

        // Already done?
        if ($user->EmailAddress == $newEmail) {
            $request->session()->flash('warning', 'You have already verified your new email address.');

            return redirect()->route('my_account.profile');
        }

        // Check no other user has claimed this since the email was sent
        $count = User::where(
            [
                ['EmailAddress', $newEmail],
                ['UserID', '!=', $user->UserID],
                ['Tenant', tenant('ID')],
                ['Active', 1],
            ]
        )->count();

        if ($count > 0) {
            // Error
            $request->session()->flash('error', $newEmail.' is already in use by another account.');
        } else {
            $user->EmailAddress = $newEmail;
            $user->save();
            $request->session()->flash('success', 'We\'ve updated your account email address to '.$newEmail.'.');
        }

        return redirect()->route('my_account.profile');
    }
}
