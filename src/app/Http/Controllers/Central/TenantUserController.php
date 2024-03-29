<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TenantUserController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        Gate::authorize('manage');

        $users = null;

        if ($request->query('query')) {
            $users = User::search($request->query('query'))->paginate(config('app.per_page'));
        } else {
            $users = User::orderBy('Forename', 'asc')->orderBy('Surname', 'asc')->paginate(config('app.per_page'));
        }

        return Inertia::render('Central/Users/Index', [
            'users' => $users->onEachSide(3),
        ]);
    }

    public function show(User $user)
    {
        Gate::authorize('manage');

        return Inertia::location('/v1/users/'.$user->UserID);
    }
}
