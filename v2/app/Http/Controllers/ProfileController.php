<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->phone;
        return Inertia::render('MyAccount/Index', [
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $attributes = $request->validate([
            'first_name' => ['required', 'max:255'],
            'last_name' => ['required', 'max:255'],
            'email' => ['required', 'email:rfc,dns', Rule::unique('users')->ignore($user->id), 'max:255'],
            'phone' => ['required', 'max:255'],
        ]);

        $user->fill([
            'first_name' => $attributes['first_name'],
            'last_name' => $attributes['last_name'],
            'email' => $attributes['email'],
        ]);

        $user->phone()->updateOrCreate(
            [
                'user_id' => $user->id
            ],
            [
                'number' => $attributes['phone']
            ]
        );

        $user->save();

        return Redirect::route("myaccount.index");
    }
}
