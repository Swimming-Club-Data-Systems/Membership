<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('first_name', 'asc')->orderBy('last_name', 'asc')->paginate(config('app.per_page'));
        return Inertia::render('User/Index', [
            'users' => $users->onEachSide(3),
        ]);
    }

    public function search(Request $request)
    {
        $users = User::search($request->search)->paginate(config('app.per_page'));
        return Inertia::render('User/Index', [
            'users' => $users->onEachSide(3),
        ]);
    }

    //
    public function show($id)
    {
        $user = User::findOrFail($id);
        ddd($user);
    }
}
