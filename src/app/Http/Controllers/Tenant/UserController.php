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

        return Inertia::location('/v1/users/'.$user->UserID);
    }

    public function combobox(Request $request): \Illuminate\Http\JsonResponse
    {
        $users = null;
        if ($request->query('query')) {
            $users = User::search($request->query('query'))
                ->where('Tenant', tenant('ID'))
                ->paginate(50);
        }

        $usersArray = [];

        $selectedUser = null;
        if ($request->query('id')) {
            /** @var User $selectedUser */
            $selectedUser = User::find($request->query('id'));
            if ($selectedUser) {
                $usersArray[] = [
                    'id' => $selectedUser->UserID,
                    'name' => $selectedUser->name,
                    'image_url' => $selectedUser->gravatar_url,
                ];
            }
        }

        if ($users) {
            foreach ($users as $user) {
                /** @var User $user */
                if ($selectedUser == null || $selectedUser->UserID !== $user->UserID) {
                    $usersArray[] = [
                        'id' => $user->UserID,
                        'name' => $user->name,
                        'image_url' => $user->gravatar_url,
                    ];
                }
            }
        }

        $responseData = [
            'data' => $usersArray,
            'has_more_pages' => $users && $users->hasMorePages(),
            'total' => $users ? $users->total() : count($usersArray),
        ];

        return \response()->json($responseData);
    }
}
