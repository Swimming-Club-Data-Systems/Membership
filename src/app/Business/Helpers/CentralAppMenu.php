<?php

namespace App\Business\Helpers;

use App\Models\Central\User;
use Illuminate\Support\Facades\Gate;

/**
 * Defines the menu for users in the tenant app
 */
class CentralAppMenu
{
    public static function asArray(User|null $user): array
    {
        $menu = [];

        $menu[] = [
            'name' => 'Clubs',
            'href' => route('tenants'),
        ];

        $menu[] = [
            'name' => 'Help and Support',
            'href' => 'https://docs.myswimmingclub.uk',
            'external' => true,
        ];

        if ($user) {

            $menu[] = [
                'name' => 'Admin',
                'href' => '/admin',
            ];

            $menu[] = [
                'name' => 'Tenant Users',
                'href' => '/users',
            ];

            if (Gate::check('viewTelescope', [$user])) {
                $menu[] = [
                    'name' => 'Telescope',
                    'href' => '/telescope',
                    'external' => true,
                ];
            }

        }

        return $menu;
    }
}
