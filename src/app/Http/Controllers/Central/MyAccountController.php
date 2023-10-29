<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Webauthn\PublicKeyCredentialSource;

class MyAccountController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('password.confirm');
    }

    public function index(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        return redirect(route('central.my_account.profile'));
        // return Inertia::render('MyAccount/Index', []);
    }

    public function profile(Request $request): Response
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        return Inertia::render('Central/MyAccount/Profile', [
            'form_initial_values' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
            ],
        ]);
    }

    public function saveProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'max:255'],
            'last_name' => ['required', 'max:255'],
            'email' => [
                'required',
                'email:rfc,dns,spoof',
                'max:255',
                Rule::unique('users', 'EmailAddress')
                    ->where(fn ($query) => $query->where('Tenant', tenant('ID'))
                        ->where('UserID', '!=', Auth::id())),
            ],
        ]);

        $user = User::find(Auth::id());

        $user->first_name = Str::ucfirst($request->input('first_name'));
        $user->last_name = Str::ucfirst($request->input('last_name'));
        if (Str::lower($request->input('email')) != $user->email) {
            $user->verifyNewEmail($request->input('email'));
        }

        $user->save();

        $flashMessage = 'We\'ve saved your changes.';

        if (Str::lower($request->input('email')) != $user->email) {
            $flashMessage .= ' Please follow the link we have sent to '.Str::lower($request->input('email')).' to finish changing your email.';
        }

        $request->session()->flash('success', $flashMessage);

        return Redirect::route('my_account.profile');
    }

    public function password(Request $request): Response
    {
        /**
         * @var User $user
         */
        $user = Auth::user();
        $passkeys = [];
        foreach ($user->userCredentials()->orderBy('credential_name')->get() as $credential) {

            $source = PublicKeyCredentialSource::createFromArray(json_decode($credential->credential, true));

            $passkeys[] = [
                'id' => $credential->id,
                'credential_id' => $credential->credential_id,
                'type' => $source->getType(),
                'name' => $credential->credential_name,
                'created_at' => $credential->created_at,
                'updated_at' => $credential->updated_at,
            ];
        }

        return Inertia::render('Central/MyAccount/Password', [
            'passkeys' => $passkeys,
        ]);
    }

    public function savePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        /**
         * @var User $user
         */
        $user = Auth::user();

        $user->forceFill([
            'Password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        return Redirect::route('my_account.email');
    }

    public function advanced(Request $request): Response
    {
        return Inertia::render('MyAccount/Advanced', []);
    }

    public function saveAdvanced(Request $request): Response
    {
        return Inertia::render('', []);
    }
}
