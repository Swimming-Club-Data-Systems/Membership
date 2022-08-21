<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\Address;
use App\Business\Helpers\Countries;
use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

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
        return Inertia::render('', []);
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
