<?php

namespace App\Business\Helpers;


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
        }

        return $menu;
    }

    public static function members(User $user) {
        $menu = [];

        $menu[] = [
            'name' => 'Home',
            'href' => '/members',
        ];

        $menu[] = [
            'name' => 'Link a new member',
            'href' => '/my-account/add-member',
        ];

        $menu[] = [
            'name' => 'Access Keys',
            'href' => '/members/access-keys',
        ];

        $menu[] = [
            'name' => 'Membership Centre',
            'href' => '/memberships',
        ];

        $menu[] = [
            'name' => 'Create New Member',
            'href' => '/members/new',
        ];

        $menu[] = [
            'name' => 'Unconnected Members',
            'href' => '/members/orphaned',
        ];

        $menu[] = [
            'name' => 'Qualifications',
            'href' => '/qualifications',
        ];

        $menu[] = [
            'name' => 'Log Books',
            'href' => '/log-books',
        ];

        return $menu;
    }

    public static function squads(User $user) {
        $menu = [];

        $menu[] = [
            'name' => 'Squad List',
            'href' => '/squads',
        ];

        $menu[] = [
            'name' => 'Squad Moves',
            'href' => '/squads/moves',
        ];

        $menu[] = [
            'name' => 'Squad Reps',
            'href' => '/squad-reps',
        ];

        return $menu;
    }

    public static function timetable(User $user) {
        $menu = [];

        $menu[] = [
            'name' => 'Home',
            'href' => '/attendance',
        ];

        $menu[] = [
            'name' => 'Take Register',
            'href' => '/attendance/register',
        ];

        $menu[] = [
            'name' => 'Timetable',
            'href' => '/timetable',
        ];

        $menu[] = [
            'name' => 'Bookings',
            'href' => '/timetable/booking',
        ];

        $menu[] = [
            'name' => 'Manage Sessions',
            'href' => '/attendance/sessions',
        ];

        $menu[] = [
            'name' => 'Manage Venues',
            'href' => '/attendance/venues',
        ];

        $menu[] = [
            'name' => 'History',
            'href' => '/attendance/history',
        ];

        return $menu;
    }

    public static function galas(User $user) {
        $menu = [];

        $menu[] = [
            'name' => 'Home',
            'href' => '/galas',
        ];

        $menu[] = [
            'name' => 'Enter Gala',
            'href' => '/galas/entergala',
        ];

        $menu[] = [
            'name' => 'My Entries',
            'href' => '/galas/entries',
        ];

        $menu[] = [
            'name' => 'Pay for Entries',
            'href' => '/galas/pay-for-entries',
        ];

        $menu[] = [
            'name' => 'Time Converter',
            'href' => '/time-converter',
        ];

        $menu[] = [
            'name' => 'Gala List',
            'href' => '/galas/all-galas',
        ];

        $menu[] = [
            'name' => 'Add New Gala',
            'href' => '/galas/addgala',
        ];

        $menu[] = [
            'name' => 'View Entries',
            'href' => '/galas/entries',
        ];

        $menu[] = [
            'name' => 'Team Manager Dashboard',
            'href' => '/team-managers',
        ];

        return $menu;
    }

    public static function payments(User $user) {
        $menu = [];

        $menu[] = [
            'name' => 'Home',
            'href' => '/payments',
        ];

        $menu[] = [
            'name' => 'Billing History',
            'href' => '/payments/statements',
        ];

        $menu[] = [
            'name' => 'Bank Account',
            'href' => '/payments/direct-debit',
        ];

        $menu[] = [
            'name' => 'Latest Statement',
            'href' => '/payments/statements/latest',
        ];

        $menu[] = [
            'name' => 'Fees Since Last Statement',
            'href' => '/payments/fees',
        ];

        $menu[] = [
            'name' => 'Squad and Extra Fees',
            'href' => '/payments/squad-fees',
        ];

        $menu[] = [
            'name' => 'Annual Membership Fees',
            'href' => '/payments/membership-fees',
        ];

        $menu[] = [
            'name' => 'Payment Status',
            'href' => '/payments/history',
        ];

        $menu[] = [
            'name' => 'Extra Fees',
            'href' => '/payments/extrafees',
        ];

        $menu[] = [
            'name' => 'Charge or Refund Gala Entries',
            'href' => '/payments/galas',
        ];

        $menu[] = [
            'name' => 'This Months Squad Fees',
            'href' => '/payments/history/squads/YEAR/MONTH',
        ];

        $menu[] = [
            'name' => 'This Months Extra Fees',
            'href' => '/payments/history/extras/YEAR/MONTH',
        ];

        $menu[] = [
            'name' => 'GoCardless Dashboard',
            'href' => 'https://manage.gocardless.com',
            'external' => true,
        ];

        $menu[] = [
            'name' => 'Stripe Dashboard',
            'href' => 'https://dashboard.stripe.com/',
            'external' => true,
        ];

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

    public static function notify(User $user) {
        $menu = [];

        $menu[] = [
            'name' => 'Compose Email',
            'href' => '/notify/new',
        ];

        $menu[] = [
            'name' => 'Targeted Lists',
            'href' => '/notify/lists',
        ];

        $menu[] = [
            'name' => 'SMS Lists',
            'href' => '/notify/sms',
        ];

        $menu[] = [
            'name' => 'Previously Sent Emails',
            'href' => '/notify/history',
        ];

        return $menu;
    }

    public static function users(User $user) {
        $menu = [];

        $menu[] = [
            'name' => 'User List',
            'href' => '/users',
        ];

        $menu[] = [
            'name' => 'New User (member onboarding)',
            'href' => '/onboarding',
        ];

        $menu[] = [
            'name' => 'New User (staff)',
            'href' => '/users/add',
        ];

        return $menu;
    }
}
