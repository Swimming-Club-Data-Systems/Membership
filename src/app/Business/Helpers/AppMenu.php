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
            ];

            $menu[] = [
                'name' => 'Squads',
                'href' => '/squads',
            ];

            if ($user->hasPermission(['Admin', 'Coach', 'Committee'])) {
                $menu[] = [
                    'name' => 'Registers',
                    'href' => '/attendance',
                ];
            } else {
                $menu[] = [
                    'name' => 'Timetable',
                    'href' => '/timetable',
                ];
            }

            $menu[] = [
                'name' => 'Galas',
                'href' => '/galas',
            ];

            $menu[] = [
                'name' => 'Payments',
                'href' => '/payments',
            ];

            $menu[] = [
                'name' => 'Notify',
                'href' => '/notify',
            ];

            if ($user->hasPermission(['Admin', 'Galas'])) {
                $menu[] = [
                    'name' => 'Users',
                    'href' => '/users',
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
}
