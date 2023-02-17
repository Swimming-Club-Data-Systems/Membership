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
        $this->authorize('viewAll', User::class);

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

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return Inertia::location('/v1/users/' . $user->UserID);
    }

    public function combobox(Request $request)
    {
        $users = null;
        if ($request->query('query')) {
            $users = User::search($request->query('query'))
                ->where('Tenant', tenant('ID'))
                ->paginate(50);
        } else {
            $users = User::orderBy('Forename', 'asc')
                ->orderBy('Surname', 'asc')
                ->paginate(config('50'));
        }

        $usersArray = [];

        $selectedUser = null;
        if ($request->query('selected')) {
            $selectedUser = User::find($request->query('selected'));
            if ($selectedUser) {
                $usersArray[] = [
                    'id' => $selectedUser->UserID,
                    'name' => $selectedUser->name,
                    'gravitar_url' => $selectedUser->gravitar_url,
                ];
            }
        }

        foreach ($users as $user) {
            /** @var User $user */
            $usersArray[] = [
                'id' => $user->UserID,
                'name' => $user->name,
                'gravitar_url' => $user->gravitar_url,
            ];
        }

        $responseData = [
            'data' => $usersArray,
            'has_more_pages' => $users->hasMorePages(),
            'total' => $users->total(),
        ];

        return \response()->json($responseData);
    }
}
