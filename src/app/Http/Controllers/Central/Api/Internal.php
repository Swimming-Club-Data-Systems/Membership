<?php

namespace App\Http\Controllers\Central\Api;

use App\Business\Helpers\AppMenu;
use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\Request;

class Internal extends Controller
{
    /**
     * Show the profile for a given user.
     *
     * @param Request $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenu(Request $request, $id)
    {
        $user = User::find($id);

        $menu = tenancy()->find($user->Tenant)->run(function () use ($user) {
            return AppMenu::asArray($user);
        });

        return response()->json($menu);
    }
}
