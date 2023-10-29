<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Mail\Central\NewTenantAdministrator;
use App\Mail\Central\NewTenantAdministratorUser;
use App\Models\Central\Tenant;
use App\Models\Central\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TenantAdministratorsController extends Controller
{
    public function index(Tenant $tenant)
    {
        $this->authorize('manage', $tenant);

        return Inertia::render('Central/Tenants/Administrators', [
            'id' => $tenant->ID,
            'name' => $tenant->Name,
            'users' => $tenant->centralUsers()->orderBy('first_name')->orderBy('last_name')->get()->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'gravatar_url' => $user->gravatar_url,
                ];
            }),
        ]);
    }

    public function create(Request $request, Tenant $tenant)
    {
        $this->authorize('manage', $tenant);

        $validated = $request->validate([
            'first_name' => ['required', 'max:255'],
            'last_name' => ['required', 'max:255'],
            'email' => [
                'required',
                'email:rfc,dns,spoof',
                'max:255',
            ],
        ]);

        /** @var User $user */
        $user = User::firstOrNew([
            'email' => trim($request->input('email')),
        ]);

        $addingToExisting = $user->exists;

        if (! $addingToExisting) {
            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');
            $user->password = Hash::make(Str::random());
            $user->save();
        }

        $user->tenants()->attach($tenant->ID);

        // Send an email
        if ($addingToExisting) {
            // Email notifying the user they have been added to a new tenant
            Mail::to($user)->send(new NewTenantAdministrator($user, $tenant));
        } else {
            // Email a signed link to allow the user to continue setup
            // $link = URL::temporarySignedRoute(
            // 'central.admin_signup', now()->addDays(3), ['user' => $user]
            // );

            $link = route('central.password.request');

            Mail::to($user)->send(new NewTenantAdministratorUser($user, $tenant, $link));
        }

        $request->session()->flash('success', $user->first_name.' has been added as an administrator for '.$tenant->Name.'.');

        return Redirect::route('central.tenants.administrators', [$tenant]);
    }

    public function delete(Request $request, Tenant $tenant, User $user)
    {
        $this->authorize('manage', $tenant);

        abort_unless($user->id !== $request->user('central')->id, 401);

        $user->tenants()->detach($tenant);

        $request->session()->flash('success', 'We have deleted '.$user->first_name.' from your list of administrators.');

        return Redirect::route('central.tenants.administrators', $tenant);
    }

    public function signUp(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        abort_unless($user->password != null, 401);

        return Inertia::render('CentralAuth/Users/Register', [

        ]);
    }

    public function update(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        abort_unless($user->password != null, 401);

        Auth::guard('central')->login($user, false);

        \redirect('central.home');
    }
}
