<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
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
        $users = null;

        if ($request->query('query')) {
            $users = User::search($request->query('query'))->where('Tenant', tenant('ID'))->paginate(config('app.per_page'));
        } else {
            $users = User::orderBy('Forename', 'asc')->orderBy('Surname', 'asc')->paginate(config('app.per_page'));
        }
        return Inertia::render('Users/Index', [
            'users' => $users->onEachSide(3),
        ]);
    }

    public function show(User $user) {
        return redirect("/v1/users/" . $user->UserID);
    }
}
