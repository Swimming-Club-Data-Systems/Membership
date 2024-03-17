<?php

namespace App\Business\Helpers;

use App\Models\Central\Tenant;
use App\Models\Tenant\Competition;
use App\Models\Tenant\Sms;
use App\Models\Tenant\Squad;
use App\Models\Tenant\User;
use App\Models\Tenant\Venue;
use Illuminate\Support\Facades\App;

/**
 * Defines the menu for users in the tenant app
 */
class AppMenu
{
    private ?bool $isTeamManager;

    private ?bool $isSquadRep;

    private ?bool $hasSquadReps;

    private ?bool $hasMembers;

    private ?User $user;

    private function __construct(?User $user)
    {
        $this->user = $user;
        $this->isTeamManager = $this->user?->galas()->where('GalaDate', '>=', now()->subDay())->exists();
        $this->isSquadRep = $this->user?->representedSquads()->exists();
        $this->hasMembers = $this->user?->members()->exists();
        $this->hasSquadReps = $this->isSquadRep || Squad::has('reps')->exists();
    }

    public function galas()
    {
        $menu = [];

        $menu[] = [
            'name' => 'Home',
            'href' => '/galas',
        ];

        if ($this->user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Enter Gala',
                'href' => '/galas/entergala',
            ];
        }

        if ($this->user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'My Entries',
                'href' => '/galas/entries',
            ];
        }

        /** @var Tenant $tenant */
        $tenant = tenant();

        if ($tenant->getOption('GALA_CARD_PAYMENTS_ALLOWED')) {
            $menu[] = [
                'name' => 'Pay for Entries',
                'href' => '/galas/pay-for-entries',
            ];
        }

        $menu[] = [
            'name' => 'Time Converter',
            'href' => '/time-converter',
        ];

        $menu[] = [
            'name' => 'Gala List',
            'href' => '/galas/all-galas',
        ];

        if ($this->user->hasPermission(['Admin', 'Galas', 'Coach'])) {
            $menu[] = [
                'name' => 'Add New Gala',
                'href' => '/galas/addgala',
            ];
        }

        if ($this->user->hasPermission(['Admin', 'Galas', 'Coach'])) {
            $menu[] = [
                'name' => 'View Entries',
                'href' => '/galas/entries',
            ];
        }

        if ($this->isTeamManager || $this->user->hasPermission(['Admin', 'Galas', 'Coach'])) {
            $menu[] = [
                'name' => 'Team Manager Dashboard',
                'href' => '/team-managers',
            ];
        }

        if ($this->user->can('create', Competition::class)) {
            $menu[] = [
                'name' => 'Guest Competitions',
                'href' => route('competitions.index'),
            ];
        }

        return $menu;
    }

    public function members()
    {
        $menu = [];

        if ($this->hasMembers) {
            $menu[] = [
                'name' => 'My Members',
                'href' => '/#members',
            ];
        }

        if ($this->user->hasPermission(['Admin', 'Galas', 'Coach'])) {
            $menu[] = [
                'name' => 'Member List',
                'href' => '/members',
            ];
        }

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'New Member',
                'href' => route('members.new'),
            ];
        }

        if ($this->user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Connect Member to Account',
                'href' => '/my-account/add-member',
            ];
        }

        if ($this->user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Emergency Contacts',
                'href' => '/emergency-contacts',
            ];
        }

        if ($this->user->hasPermission(['Admin', 'Coach'])) {
            $menu[] = [
                'name' => 'Access Keys',
                'href' => '/members/access-keys',
            ];
        }

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'Membership Centre',
                'href' => '/memberships',
            ];
        }

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'Unconnected Members',
                'href' => '/members/orphaned',
            ];
        }

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'Qualifications',
                'href' => '/qualifications',
            ];
        }

        if ($this->user->hasPermission(['Admin', 'Coach', 'Galas'])) {
            $menu[] = [
                'name' => 'Log Books',
                'href' => '/log-books',
            ];
        }

        $menu[] = [
            'name' => 'COVID Tools (Deprecated)',
            'href' => '/covid',
        ];

        return $menu;
    }

    public static function asArray(?User $user): array
    {
        $object = new AppMenu($user);

        return $object->getMenu();
    }

    private function getMenu(): array
    {
        $menu = [];

        if ($this->user) {

            $menu[] = [
                'name' => 'Members',
                'href' => '/members',
                'children' => $this->members(),
            ];

            $menu[] = [
                'name' => 'Squads',
                'href' => '/squads',
                'children' => $this->squads(),
            ];

            if ($this->user->hasPermission(['Admin', 'Coach', 'Committee'])) {
                $menu[] = [
                    'name' => 'Registers',
                    'href' => '/attendance',
                    'children' => $this->timetable(),
                ];
            } else {
                $menu[] = [
                    'name' => 'Timetable',
                    'href' => '/timetable',
                    'children' => $this->timetable(),
                ];
            }

            $menu[] = [
                'name' => 'Galas',
                'href' => '/galas',
                'children' => $this->galas(),
            ];

            // Disabled
            //            if (App::isLocal()) {
            //                $menu[] = [
            //                    'name' => 'Competitions',
            //                    'href' => '/competitions',
            //                    'children' => $this->competitions(),
            //                ];
            //            }

            if ($this->user->hasPermission(['Admin', 'Galas'])) {
                $menu[] = [
                    'name' => 'Users',
                    'href' => '/users',
                    'children' => $this->users(),
                ];
            }

            $menu[] = [
                'name' => 'Pay',
                'href' => '/payments',
                'children' => $this->payments(),
            ];

            if (App::isLocal()) {
                $menu[] = [
                    'name' => 'Billing',
                    'href' => '/billing',
                    'children' => $this->paymentsV2(),
                ];
            }

            if ($this->isSquadRep || $this->user->hasPermission(['Admin', 'Galas', 'Coach'])) {
                $menu[] = [
                    'name' => 'Notify',
                    'href' => '/notify',
                    'children' => $this->notify(),
                ];
            }

            if ($this->user->hasPermission('Admin')) {
                $menu[] = [
                    'name' => 'Admin',
                    'href' => '/admin',
                    'children' => $this->admin(),
                ];
            }
        } else {
            $menu[] = [
                'name' => 'Login',
                'href' => route('login', [], false),
            ];

            $menu[] = [
                'name' => 'Timetable',
                'href' => '/timetable',
            ];

            $menu[] = [
                'name' => 'Time Converter',
                'href' => '/timeconverter',
            ];

            $menu[] = [
                'name' => 'Log Books',
                'href' => '/log-books',
            ];

            $menu[] = [
                'name' => 'Help',
                'href' => 'https://docs.myswimmingclub.uk',
                'external' => true,
            ];
        }

        return $menu;
    }

    public function squads()
    {
        $menu = [];

        if ($this->user->hasPermission(['Admin', 'Coach'])) {
            $menu[] = [
                'name' => 'Squad List',
                'href' => route('squads.index', [], false),
            ];
        }

        if ($this->user->hasPermission(['Admin', 'Coach'])) {
            $menu[] = [
                'name' => 'Squad Moves',
                'href' => route('squad-moves.index', [], false),
            ];
        }

        if ($this->hasSquadReps || $this->user->hasPermission(['Admin', 'Coach'])) {
            $menu[] = [
                'name' => 'Squad Reps',
                'href' => '/squad-reps',
            ];
        }

        return $menu;
    }

    public function timetable()
    {
        $menu = [];

        if ($this->user->hasPermission(['Admin', 'Coach'])) {
            $menu[] = [
                'name' => 'Home',
                'href' => '/attendance',
            ];
        }

        if ($this->user->hasPermission(['Admin', 'Coach'])) {
            $menu[] = [
                'name' => 'Take Register',
                'href' => '/attendance/register',
            ];
        }

        $menu[] = [
            'name' => 'Timetable',
            'href' => '/timetable',
        ];

        $menu[] = [
            'name' => 'Bookings',
            'href' => '/timetable/booking',
        ];

        if ($this->user->hasPermission(['Admin', 'Coach'])) {
            $menu[] = [
                'name' => 'Manage Sessions',
                'href' => '/attendance/sessions',
            ];
        }

        if ($this->user->hasPermission(['Admin'])) {
            $menu[] = [
                'name' => 'Manage Venues',
                'href' => '/attendance/venues',
            ];
        }

        if ($this->user->hasPermission(['Admin', 'Coach'])) {
            $menu[] = [
                'name' => 'History',
                'href' => '/attendance/history',
            ];
        }

        return $menu;
    }

    public function users()
    {
        $menu = [];

        if ($this->user->hasPermission(['Admin', 'Galas'])) {
            $menu[] = [
                'name' => 'User List',
                'href' => '/users',
            ];
        }

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'New User (member onboarding)',
                'href' => '/onboarding',
            ];
        }

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'New User (staff)',
                'href' => '/users/add',
            ];
        }

        return $menu;
    }

    public function payments()
    {
        $menu = [];

        $menu[] = [
            'name' => 'Home',
            'href' => '/payments',
        ];

        if ($this->user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Billing History',
                'href' => '/payments/statements',
            ];
        }

        if ($this->user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Bank Account',
                'href' => '/payments/direct-debit',
            ];
        }

        if ($this->user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Latest Statement',
                'href' => '/payments/statements/latest',
            ];
        }

        if ($this->user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Fees Since Last Statement',
                'href' => '/payments/fees',
            ];
        }

        if ($this->user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Squad and Extra Fees',
                'href' => '/payments/squad-fees',
            ];
        }

        if ($this->user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Annual Membership Fees',
                'href' => '/payments/membership-fees',
            ];
        }

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'Payment Status',
                'href' => '/payments/history',
            ];
        }

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'Extra Fees',
                'href' => '/payments/extrafees',
            ];
        }

        if ($this->user->hasPermission(['Admin', 'Galas'])) {
            $menu[] = [
                'name' => 'Charge or Refund Gala Entries',
                'href' => '/payments/galas',
            ];
        }

        $today = new \DateTime('now', new \DateTimeZone(config('app.timezone')));

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'This Months Squad Fees',
                'href' => '/payments/history/squads/'.$today->format('Y').'/'.$today->format('m'),
            ];
        }

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'This Months Extra Fees',
                'href' => '/payments/history/extras/'.$today->format('Y').'/'.$today->format('m'),
            ];
        }

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'GoCardless Dashboard',
                'href' => 'https://manage.gocardless.com',
                'external' => true,
            ];
        }

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'Stripe Dashboard',
                'href' => 'https://dashboard.stripe.com/',
                'external' => true,
            ];
        }

        $menu[] = [
            'name' => 'Credit and Debit Cards',
            'href' => '/payments/cards',
        ];

        $menu[] = [
            'name' => 'Card Transactions',
            'href' => '/payments/card-transactions',
        ];

        $menu[] = [
            'name' => 'Add Card',
            'href' => '/payments/cards/add',
        ];

        return $menu;
    }

    public function paymentsV2()
    {
        $menu = [];

        $menu[] = [
            'name' => 'Home',
            'href' => '/payments',
        ];

        $menu[] = [
            'name' => 'Payment Methods',
            'href' => route('payments.methods.index'),
        ];

        $menu[] = [
            'name' => 'Ledgers and Journals',
            'href' => route('payments.ledgers.index'),
        ];

        if ($this->user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Statements',
                'href' => route('payments.statements.index'),
            ];
        }

        if ($this->user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Transactions',
                'href' => route('payments.transactions.index'),
            ];
        }

        if ($this->user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Payments',
                'href' => route('payments.payments.index'),
            ];
        }

        if ($this->user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Squad and Extra Fees',
                'href' => '/payments/squad-fees',
            ];
        }

        if ($this->user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Annual Membership Fees',
                'href' => '/payments/membership-fees',
            ];
        }

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'Payment Status',
                'href' => '/payments/history',
            ];
        }

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'Extra Fees',
                'href' => '/payments/extrafees',
            ];
        }

        $today = new \DateTime('now', new \DateTimeZone(config('app.timezone')));

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'This Months Squad Fees',
                'href' => '/payments/history/squads/'.$today->format('Y').'/'.$today->format('m'),
            ];
        }

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'This Months Extra Fees',
                'href' => '/payments/history/extras/'.$today->format('Y').'/'.$today->format('m'),
            ];
        }

        if ($this->user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'Stripe Dashboard',
                'href' => 'https://dashboard.stripe.com/',
                'external' => true,
            ];
        }

        return $menu;
    }

    public function notify()
    {
        $menu = [];

        $menu[] = [
            'name' => 'Compose Email',
            'href' => '/notify/new',
        ];

        if ($this->user->can('create', Sms::class)) {
            $menu[] = [
                'name' => 'Compose SMS',
                'href' => route('notify.sms.new', [], false),
            ];
        }

        if ($this->user->hasPermission(['Admin', 'Galas', 'Coach'])) {
            $menu[] = [
                'name' => 'Targeted Lists',
                'href' => '/notify/lists',
            ];
        }

        if ($this->user->hasPermission(['Admin', 'Galas', 'Coach'])) {
            $menu[] = [
                'name' => 'Full Email History',
                'href' => route('notify.email.history', [], false),
            ];
        }

        if ($this->user->can('view', Sms::class)) {
            $menu[] = [
                'name' => 'Full SMS History',
                'href' => route('notify.sms.history', [], false),
            ];
        }

        $menu[] = [
            'name' => 'Received Email History',
            'href' => '/my-account/notify-history',
        ];

        return $menu;
    }

    public function admin()
    {
        $menu = [];

        $menu[] = [
            'name' => 'Admin tools',
            'href' => '/admin',
        ];

        $menu[] = [
            'name' => 'Pages',
            'href' => '/pages',
        ];

        $menu[] = [
            'name' => 'Reports',
            'href' => '/admin/reports',
        ];

        $menu[] = [
            'name' => 'System Settings',
            'href' => '/v1/settings', // Added /v1 and there is a route at /settings redirecting
        ];

        if ($this->user->can('create', Venue::class)) {
            $menu[] = [
                'name' => 'Venues (Competitions)',
                'href' => route('venues.index', [], false),
            ];
        }

        return $menu;
    }

    public function competitions()
    {
        $menu = [];

        $menu[] = [
            'name' => 'Home',
            'href' => route('competitions.index'),
        ];

        return $menu;
    }
}
