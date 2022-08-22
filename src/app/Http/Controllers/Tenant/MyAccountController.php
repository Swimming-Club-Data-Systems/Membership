<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\Address;
use App\Business\Helpers\Countries;
use App\Business\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\Tenant\NotifyCategories;
use App\Models\Tenant\User;
use App\Rules\ValidPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

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

    public function index(Request $request): Response
    {
        return Inertia::render('MyAccount/Index', []);
    }

    public function profile(Request $request): Response
    {
        /**
         * @var User $user
         */
        $user = Auth::user();
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
                    ->where(fn($query) => $query->where('Tenant', tenant('ID'))
                        ->where('UserID', '!=', Auth::id()))
            ],
            'mobile' => [new ValidPhone, 'max:255'],
            ...Address::validationRules(),
        ]);

        $user = User::find(Auth::id());
        $address = $user->getAddress();

        $user->Forename = Str::ucfirst($request->input('first_name'));
        $user->Surname = Str::ucfirst($request->input('last_name'));
        if (Str::lower($request->input('email')) != $user->EmailAddress) {
            $user->verifyNewEmail($request->input('email'));
        }
        $user->Mobile = PhoneNumber::toDatabaseFormat($request->input('mobile'));

        $address->address_line_1 = Str::title($request->input('address_line_1'));
        $address->address_line_2 = Str::title($request->input('address_line_2'));
        $address->city = Str::title($request->input('city'));
        $address->county = Str::title($request->input('county'));
        $address->country_code = Str::upper($request->input('country'));
        $address->post_code = $request->input('post_code');

        $user->setOption('MAIN_ADDRESS', (string)$address);

        $user->save();

        $flashMessage = 'We\'ve saved your changes.';

        if (Str::lower($request->input('email')) != $user->EmailAddress) {
            $flashMessage .= ' Please follow the link we have sent to ' . Str::lower($request->input('email')) . ' to finish changing your email.';
        }

        $request->session()->flash('success', $flashMessage);

        return Redirect::route('my_account.profile');
    }

    public function email(Request $request): Response
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        $notifySubOpts = [];
        $notifySubOptsFormik = [];
        $notifyAdditionalEmails = [];

        foreach (NotifyCategories::where('Active', true)->orderBy('Name')->get() as $sub) {
            $subscribed = $sub->users()->wherePivot('UserID', $user->UserID)->first()?->subscription->Subscribed ?? false;

            $notifySubOpts[] = [
                'id' => $sub->ID,
                'name' => $sub->Name,
                'description' => Str::finish($sub->Description, '.'),
            ];

            $notifySubOptsFormik[$sub->ID] = (bool)$subscribed;
        }

        foreach ($user->notifyAdditionalEmails()->orderBy('Name')->orderBy('EmailAddress')->get() as $recipient) {
            $notifyAdditionalEmails[] = [
                'id' => $recipient->ID,
                'name' => $recipient->Name,
                'email' => $recipient->EmailAddress,
            ];
        }

        return Inertia::render('MyAccount/Email', [
            'notify_categories' => $notifySubOpts,
            'notify_additional_emails' => $notifyAdditionalEmails,
            'form_initial_values' => [
                'email' => $user->EmailAddress,
                'email_comms' => (bool)$user->EmailComms,
                'notify_categories' => $notifySubOptsFormik,
            ],
        ]);
    }

    public function saveEmail(Request $request): RedirectResponse
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        $validated = $request->validate([
            'email' => [
                'required',
                'email:rfc,dns,spoof',
                'max:255',
                Rule::unique('users', 'EmailAddress')
                    ->where(fn($query) => $query->where('Tenant', tenant('ID'))
                        ->where('UserID', '!=', Auth::id()))
            ],
        ]);

        // Check and verify new email
        if (Str::lower($request->input('email')) != $user->EmailAddress) {
            $user->verifyNewEmail($request->input('email'));
        }

        $user->EmailComms = $request->boolean('email_comms');

        foreach (NotifyCategories::where('Active', true)->get() as $sub) {

            // Does the user have one?
            $userSub = $user->notifyCategories()->where('notifyCategories.ID', $sub->ID)->first();

            if ($request->boolean('notify_categories.' . $sub->ID) && !$userSub) {
                $user->notifyCategories()->attach($sub);

                $userSub = $user->notifyCategories()->where('notifyCategories.ID', $sub->ID)->first();
                $userSub->subscription->Subscribed = true;
                $userSub->subscription->save();
            } else {
                $user->notifyCategories()->detach($sub);
            }
        }

        $user->save();

        $flashMessage = 'We\'ve saved your changes.';

        if (Str::lower($request->input('email')) != $user->EmailAddress) {
            $flashMessage .= ' Please follow the link we have sent to ' . Str::lower($request->input('email')) . ' to finish changing your email.';
        }

        $request->session()->flash('success', $flashMessage);

        return Redirect::route('my_account.email');
    }

    public function password(Request $request): Response
    {
        return Inertia::render('MyAccount/Password', []);
    }

    public function savePassword(Request $request): Response
    {
        return Inertia::render('', []);
    }

    public function advanced(Request $request): Response
    {
        return Inertia::render('MyAccount/Advanced', []);
    }

    public function saveAdvanved(Request $request): Response
    {
        return Inertia::render('', []);
    }

}
