<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = null;

        if ($request->search) {
            $users = User::search($request->search)->paginate(config('app.per_page'));
        } else {
            $users = User::orderBy('first_name', 'asc')->orderBy('last_name', 'asc')->paginate(config('app.per_page'));
        }
        return Inertia::render('User/Index', [
            'users' => $users->onEachSide(3),
        ]);
    }

    //
    public function show($id)
    {
        $user = User::findOrFail($id);
        return Inertia::render('User/Show', [
            'user' => $user,
        ]);
    }
}
