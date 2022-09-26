<?php

namespace App\Business\Helpers;


use App\Models\Central\Tenant;
use App\Models\Tenant\User;

/**
 * Defines the menu for users in the tenant app
 */
class AppMenu
{
    public static function asArray(User|null $user): array
    {
        $menu = [];

        if ($user) {

            $menu[] = [
                'name' => 'Members',
                'href' => '/members',
                'children' => self::members($user),
            ];

            $menu[] = [
                'name' => 'Squads',
                'href' => '/squads',
                'children' => self::squads($user),
            ];

            if ($user->hasPermission(['Admin', 'Coach', 'Committee'])) {
                $menu[] = [
                    'name' => 'Registers',
                    'href' => '/attendance',
                    'children' => self::timetable($user),
                ];
            } else {
                $menu[] = [
                    'name' => 'Timetable',
                    'href' => '/timetable',
                    'children' => self::timetable($user),
                ];
            }

            $menu[] = [
                'name' => 'Galas',
                'href' => '/galas',
                'children' => self::galas($user),
            ];

            $menu[] = [
                'name' => 'Payments',
                'href' => '/payments',
                'children' => self::payments($user),
            ];

            $menu[] = [
                'name' => 'Notify',
                'href' => '/notify',
                'children' => self::notify($user),
            ];

            if ($user->hasPermission(['Admin', 'Galas'])) {
                $menu[] = [
                    'name' => 'Users',
                    'href' => '/users',
                    'children' => self::users($user),
                ];
            }

            if ($user->hasPermission('Admin')) {
                $menu[] = [
                    'name' => 'Admin',
                    'href' => '/admin',
                ];
            }

            $menu[] = [
                'name' => 'COVID',
                'href' => '/covid',
            ];
        } else {
            $menu[] = [
                'name' => 'Login',
                'href' => route('login'),
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

    public static function members(User $user)
    {
        $menu = [];

        if ($user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'My Members',
                'href' => '/#members',
            ];
        }

        if ($user->hasPermission(['Admin', 'Galas', 'Coach'])) {
            $menu[] = [
                'name' => 'Member List',
                'href' => '/members',
            ];
        }

        if ($user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Link a new member',
                'href' => '/my-account/add-member',
            ];
        }

        if ($user->hasPermission(['Admin', 'Coach'])) {
            $menu[] = [
                'name' => 'Access Keys',
                'href' => '/members/access-keys',
            ];
        }

        if ($user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'Membership Centre',
                'href' => '/memberships',
            ];
        }

        if ($user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'Create New Member',
                'href' => '/members/new',
            ];
        }

        if ($user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'Unconnected Members',
                'href' => '/members/orphaned',
            ];
        }

        if ($user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'Qualifications',
                'href' => '/qualifications',
            ];
        }

        if ($user->hasPermission(['Admin', 'Coach', 'Galas'])) {
            $menu[] = [
                'name' => 'Log Books',
                'href' => '/log-books',
            ];
        }

        return $menu;
    }

    public static function squads(User $user)
    {
        $menu = [];

        if ($user->hasPermission(['Admin', 'Coach'])) {
            $menu[] = [
                'name' => 'Squad List',
                'href' => '/squads',
            ];
        }

        if ($user->hasPermission(['Admin', 'Coach'])) {
            $menu[] = [
                'name' => 'Squad Moves',
                'href' => '/squads/moves',
            ];
        }

        $menu[] = [
            'name' => 'Squad Reps',
            'href' => '/squad-reps',
        ];

        return $menu;
    }

    public static function timetable(User $user)
    {
        $menu = [];

        if ($user->hasPermission(['Admin', 'Coach'])) {
            $menu[] = [
                'name' => 'Home',
                'href' => '/attendance',
            ];
        }

        if ($user->hasPermission(['Admin', 'Coach'])) {
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

        if ($user->hasPermission(['Admin', 'Coach'])) {
            $menu[] = [
                'name' => 'Manage Sessions',
                'href' => '/attendance/sessions',
            ];
        }

        if ($user->hasPermission(['Admin'])) {
            $menu[] = [
                'name' => 'Manage Venues',
                'href' => '/attendance/venues',
            ];
        }

        if ($user->hasPermission(['Admin', 'Coach'])) {
            $menu[] = [
                'name' => 'History',
                'href' => '/attendance/history',
            ];
        }

        return $menu;
    }

    public static function galas(User $user)
    {
        $menu = [];

        $menu[] = [
            'name' => 'Home',
            'href' => '/galas',
        ];

        if ($user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Enter Gala',
                'href' => '/galas/entergala',
            ];
        }

        if ($user->hasPermission('Parent')) {
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

        if ($user->hasPermission(['Admin', 'Galas', 'Coach'])) {
            $menu[] = [
                'name' => 'Add New Gala',
                'href' => '/galas/addgala',
            ];
        }

        if ($user->hasPermission(['Admin', 'Galas', 'Coach'])) {
            $menu[] = [
                'name' => 'View Entries',
                'href' => '/galas/entries',
            ];
        }

        if ($user->hasPermission(['Admin', 'Galas', 'Parent', 'Coach'])) {
            $menu[] = [
                'name' => 'Team Manager Dashboard',
                'href' => '/team-managers',
            ];
        }

        return $menu;
    }

    public static function payments(User $user)
    {
        $menu = [];

        $menu[] = [
            'name' => 'Home',
            'href' => '/payments',
        ];

        if ($user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Billing History',
                'href' => '/payments/statements',
            ];
        }

        if ($user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Bank Account',
                'href' => '/payments/direct-debit',
            ];
        }

        if ($user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Latest Statement',
                'href' => '/payments/statements/latest',
            ];
        }

        if ($user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Fees Since Last Statement',
                'href' => '/payments/fees',
            ];
        }

        if ($user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Squad and Extra Fees',
                'href' => '/payments/squad-fees',
            ];
        }

        if ($user->hasPermission('Parent')) {
            $menu[] = [
                'name' => 'Annual Membership Fees',
                'href' => '/payments/membership-fees',
            ];
        }

        if ($user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'Payment Status',
                'href' => '/payments/history',
            ];
        }

        if ($user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'Extra Fees',
                'href' => '/payments/extrafees',
            ];
        }

        if ($user->hasPermission(['Admin', 'Galas'])) {
            $menu[] = [
                'name' => 'Charge or Refund Gala Entries',
                'href' => '/payments/galas',
            ];
        }

        $today = new \DateTime('now', new \DateTimeZone(config('app.timezone')));

        if ($user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'This Months Squad Fees',
                'href' => '/payments/history/squads/' . $today->format('Y') . '/' . $today->format('m'),
            ];
        }

        if ($user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'This Months Extra Fees',
                'href' => '/payments/history/extras/' . $today->format('Y') . '/' . $today->format('m'),
            ];
        }

        if ($user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'GoCardless Dashboard',
                'href' => 'https://manage.gocardless.com',
                'external' => true,
            ];
        }

        if ($user->hasPermission('Admin')) {
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

    public static function notify(User $user)
    {
        $menu = [];

        if ($user->hasPermission(['Admin', 'Galas', 'Coach'])) {
            $menu[] = [
                'name' => 'Compose Email',
                'href' => '/notify/new',
            ];
        }

        if ($user->hasPermission(['Admin', 'Galas', 'Coach'])) {
            $menu[] = [
                'name' => 'Targeted Lists',
                'href' => '/notify/lists',
            ];
        }

        if ($user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'SMS Lists',
                'href' => '/notify/sms',
            ];
        }

        if ($user->hasPermission(['Admin', 'Galas', 'Coach'])) {
            $menu[] = [
                'name' => 'Full Email History',
                'href' => '/notify/history',
            ];
        }

        $menu[] = [
            'name' => 'Received Email History',
            'href' => '/my-account/notify-history',
        ];

        return $menu;
    }

    public static function users(User $user)
    {
        $menu = [];

        if ($user->hasPermission(['Admin', 'Galas'])) {
            $menu[] = [
                'name' => 'User List',
                'href' => '/users',
            ];
        }

        if ($user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'New User (member onboarding)',
                'href' => '/onboarding',
            ];
        }

        if ($user->hasPermission('Admin')) {
            $menu[] = [
                'name' => 'New User (staff)',
                'href' => '/users/add',
            ];
        }

        return $menu;
    }
}
