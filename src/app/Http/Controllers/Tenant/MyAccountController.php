<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\Address;
use App\Business\Helpers\Countries;
use App\Business\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Rules\ValidPhone;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmailChange;

class MyAccountController extends Controller
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

    public function index(Request $request)
    {
        return Inertia::render('MyAccount/Index', []);
    }

    public function profile(Request $request)
    {
        $user = User::find(Auth::id());
        $address = $user->getAddress();

        return Inertia::render('MyAccount/Profile', [
            'form_initial_values' => [
                'first_name' => $user->Forename,
                'last_name' => $user->Surname,
                'email' => $user->EmailAddress,
                'mobile' => $user->Mobile,
                'email_subscription' => $user->EmailComms,
                'mobile_subscription' => $user->MobileComms,
                'address_line_1' => $address->address_line_1,
                'address_line_2' => $address->address_line_2,
                'city' => $address->city,
                'county' => $address->county,
                'post_code' => $address->post_code,
                'country' => $address->country_code,
            ],
            'countries' => Countries::all(),
        ]);
    }

    public function saveProfile(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'max:255'],
            'last_name' => ['required', 'max:255'],
            'email' => [
                'required',
                'email:rfc,dns,spoof',
                'max:255',
                Rule::unique('users', 'EmailAddress')->where(fn ($query) => $query->where('Tenant', tenant('ID'))->where('UserID', '!=', Auth::id()))
            ],
            'mobile' => [new ValidPhone, 'max:255'],
            ...Address::validationRules(),
        ]);

        $user = User::find(Auth::id());
        $address = $user->getAddress();

        $user->Forename = Str::ucfirst($request->input('first_name'));
        $user->Surname = Str::ucfirst($request->input('last_name'));
        if (Str::lower($request->input('email')) != $user->EmailAddress) {
            // User has changed their email
            // The email is not already in use for this tenant
            // Send a signed link to the new email to confirm
            $url = URL::temporarySignedRoute(
                'verification.verify_change',
                now()->addDay(),
                ['user' => Auth::id(), 'email' => Str::lower($request->input('email'))]
            );

            Mail::to($user)->send(new VerifyEmailChange($user, $url));
        }
        $user->Mobile = PhoneNumber::toDatabaseFormat($request->input('mobile'));

        $address->address_line_1 = Str::title($request->input('address_line_1'));
        $address->address_line_2 = Str::title($request->input('address_line_2'));
        $address->city = Str::title($request->input('city'));
        $address->county = Str::title($request->input('county'));
        $address->country_code = Str::upper($request->input('country'));
        $address->post_code = $request->input('post_code');

        $user->setOption('MAIN_ADDRESS', (string) $address);

        $user->save();

        $request->session()->flash('success', 'We\'ve saved your changes.');

        return Redirect::route('my_account.profile');
    }

    public function email(Request $request)
    {
        return Inertia::render('MyAccount/Email', []);
    }

    public function saveEmail(Request $request)
    {
        return Inertia::render('', []);
    }

    public function password(Request $request)
    {
        return Inertia::render('MyAccount/Password', []);
    }

    public function savePassword(Request $request)
    {
        return Inertia::render('', []);
    }

    public function advanced(Request $request)
    {
        return Inertia::render('MyAccount/Advanced', []);
    }

    public function saveAdvanved(Request $request)
    {
        return Inertia::render('', []);
    }
}
