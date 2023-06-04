<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Auth\V1Login;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class V1LoginController extends Controller
{
    /**
     * Handle login requests for the V1 application
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // Get current user
        $user = $request->user();

        $url = self::getUrl($user);

        $v1Login = new V1Login();
        $v1Login->token = Str::random(512);

        $user->v1Logins()->save($v1Login);

        return Inertia::location($url);
    }

    public static function getUrl(User $user): string
    {
        $v1Login = new V1Login();
        $v1Login->token = Str::random(512);

        $user->v1Logins()->save($v1Login);

        return '/v1/login-to-v1?auth_code='.urlencode($v1Login->token);
    }
}
