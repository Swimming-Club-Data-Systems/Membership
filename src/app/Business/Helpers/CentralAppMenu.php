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
            'href' => route('central.clubs'),
        ];

        $menu[] = [
            'name' => 'Help and Support',
            'href' => 'https://docs.myswimmingclub.uk',
            'external' => true,
        ];

        if ($user) {

            $menu[] = [
                'name' => 'Tenants',
                'href' => route('central.tenants'),
            ];

            if (Gate::allows('manage')) {
                $menu[] = [
                    'name' => 'Tenant Users',
                    'href' => route('central.tenant_users.index'),
                ];
            }

            if (Gate::check('viewTelescope', [$user])) {
                $menu[] = [
                    'name' => 'Telescope',
                    'href' => '/telescope',
                    'external' => true,
                ];
            }

            if (Gate::allows('manage')) {
                $menu[] = [
                    'name' => 'Notify',
                    'href' => route('central.notify'),
                ];
            }

        }

        return $menu;
    }
}
