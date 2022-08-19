<?php

namespace App\Http\Controllers\Tenant\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Auth\V1Login;
use Illuminate\Support\Str;
use Inertia\Inertia;

class V1LoginController extends Controller
{
    /**
     * Handle login requests for the V1 application
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // Get current user
        $user = $request->user();

        $v1Login = new V1Login();
        $v1Login->token = Str::random(512);

        $user->v1Logins()->save($v1Login);

        return Inertia::location("/v1/login-to-v1?auth_code=" . urlencode($v1Login->token));
    }
}
