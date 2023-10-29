<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\Address;
use App\Business\Helpers\Countries;
use App\Business\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Mail\VerifyNotifyAdditionalEmail;
use App\Models\Central\Tenant;
use App\Models\Tenant\NotifyCategory;
use App\Models\Tenant\User;
use App\Rules\ValidPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
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
        $this->middleware('auth');
        $this->middleware('password.confirm');
    }

    public function index(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        return redirect(route('my_account.profile'));
        // return Inertia::render('MyAccount/Index', []);
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
                    ->where(fn ($query) => $query->where('Tenant', tenant('ID'))
                        ->where('UserID', '!=', Auth::id())),
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

        $user->setOption('MAIN_ADDRESS', (string) $address);

        $user->save();

        $flashMessage = 'We\'ve saved your changes.';

        if (Str::lower($request->input('email')) != $user->EmailAddress) {
            $flashMessage .= ' Please follow the link we have sent to '.Str::lower($request->input('email')).' to finish changing your email.';
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

        foreach (NotifyCategory::where('Active', true)->orderBy('Name')->get() as $sub) {
            $subscribed = $sub->users()->wherePivot('UserID', $user->UserID)->first()?->subscription->Subscribed ?? false;

            $notifySubOpts[] = [
                'id' => $sub->ID,
                'name' => $sub->Name,
                'description' => Str::finish($sub->Description, '.'),
            ];

            $notifySubOptsFormik[$sub->ID] = (bool) $subscribed;
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
                'email_comms' => (bool) $user->EmailComms,
                'sms_comms' => (bool) $user->MobileComms,
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
                    ->where(fn ($query) => $query->where('Tenant', tenant('ID'))
                        ->where('UserID', '!=', Auth::id())),
            ],
        ]);

        // Check and verify new email
        if (Str::lower($request->input('email')) != $user->EmailAddress) {
            $user->verifyNewEmail($request->input('email'));
        }

        $user->EmailComms = $request->boolean('email_comms');
        $user->MobileComms = $request->boolean('sms_comms');

        foreach (NotifyCategory::where('Active', true)->get() as $sub) {

            // Does the user have one?
            $userSub = $user->notifyCategories()->where('notifyCategories.ID', $sub->ID)->first();

            $checked = $request->boolean('notify_categories.'.$sub->ID);

            if ($checked && ! $userSub) {
                $user->notifyCategories()->attach($sub);

                $userSub = $user->notifyCategories()->where('notifyCategories.ID', $sub->ID)->first();
                $userSub->subscription->Subscribed = true;
                $userSub->subscription->save();
            } elseif (! $checked) {
                $user->notifyCategories()->detach($sub);
            }
        }

        $user->save();

        $flashMessage = 'We\'ve saved your changes.';

        if (Str::lower($request->input('email')) != $user->EmailAddress) {
            $flashMessage .= ' Please follow the link we have sent to '.Str::lower($request->input('email')).' to finish changing your email.';
        }

        $request->session()->flash('success', $flashMessage);

        return Redirect::route('my_account.email');
    }

    public function saveAdditionalEmail(Request $request): RedirectResponse
    {
        // Validate response
        $validated = $request->validate([
            'email' => [
                'required',
                'max:50',
            ],
            'email' => [
                'required',
                'email:rfc,dns,spoof',
                'max:100',
                Rule::unique('notifyAdditionalEmails', 'EmailAddress')
                    ->where(fn ($query) => $query
                        ->where('UserID', '!=', Auth::id())),
            ],
        ]);

        // Check if the user already has the recipient

        /**
         * @var User $user
         */
        $user = Auth::user();

        $name = Str::title($request->input('name'));
        $email = Str::lower($request->input('email'));

        // Create a signed link for confirmation
        $url = URL::temporarySignedRoute(
            'notify_additional_emails.view',
            now()->addDay(),
            [
                'data' => urlencode(json_encode([
                    'user' => $user->UserID,
                    'name' => $name,
                    'email' => $email,
                ])),
            ]
        );

        $recipient = new \stdClass();
        $recipient->email = $email;
        $recipient->name = $name;

        Mail::to($recipient)->send(new VerifyNotifyAdditionalEmail($user, $url, $email, $name));

        $request->session()->flash('flash_bag.additional_email.success',
            'We have sent an email to '.$name.' asking them to confirm they wish to receive squad update emails.');

        return Redirect::route('my_account.email');
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

        $hasTotp = (bool) $user->getOption('hasGoogleAuth2FA');

        return Inertia::render('MyAccount/Password', [
            'passkeys' => $passkeys,
            'has_totp' => $hasTotp,
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

    public function createTOTP(Request $request)
    {
        // Check
        /** @var User $user */
        $user = Auth::user();

        /** @var Tenant $tenant */
        $tenant = tenant();

        // If the user has totp already, alert user we'll be replacing the old one
        $hasTotp = (bool) $user->getOption('hasGoogleAuth2FA');

        $g2fa = new Google2FA();

        $key = $g2fa->generateSecretKey();

        $request->session()->put('2fa_key', $key);

        $url = $g2fa->getQRCodeUrl($tenant->getOption('CLUB_NAME'), $user->EmailAddress, $key);

        QrCode::generate('Make me into a QrCode!');

        $qr = QrCode::size(100)->format('png')->generate($url);
        $qr2x = QrCode::size(200)->format('png')->generate($url);
        $qr3x = QrCode::size(300)->format('png')->generate($url);

        $img = 'data:image/png;base64,'.base64_encode($qr);
        $img2x = 'data:image/png;base64,'.base64_encode($qr2x);
        $img3x = 'data:image/png;base64,'.base64_encode($qr3x);

        return response()->json([
            'has_totp' => $hasTotp,
            'key' => $key,
            'url' => $url,
            'image' => $img,
            'image2x' => $img2x,
            'image3x' => $img3x,
        ]);
    }

    public function saveTOTP(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'min:6',
                'max:6',
            ],
        ]);

        $key = $request->session()->pull('2fa_key');
        if (! $key) {
            $request->session()->flash('flash_bag.totp_modal.error', 'Please request a TOTP key first.');

            return Redirect::route('my_account.security');
        }

        $g2fa = new Google2FA();
        $valid = $g2fa->verifyKey($key, $request->input('code'));

        if (! $valid) {
            $request->session()->flash('flash_bag.totp_modal.error', 'You entered an invalid authentication code.');

            return Redirect::route('my_account.security');
        }

        /** @var User $user */
        $user = Auth::user();

        $user->setOption('hasGoogleAuth2FA', true);
        $user->setOption('GoogleAuth2FASecret', $key);

        $request->session()->flash('flash_bag.totp.success', 'You have set up your Time-based One-Time Password application.');

        return Redirect::route('my_account.security');
    }

    public function deleteTOTP(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $user->setOption('hasGoogleAuth2FA', false);
        $user->setOption('GoogleAuth2FASecret', null);

        $request->session()->flash('flash_bag.totp.success', 'Your two-factor authentication app has now been disabled.');

        return Redirect::route('my_account.security');
    }
}
