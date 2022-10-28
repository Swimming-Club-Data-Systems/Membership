<?php

namespace App\Http\Controllers\Central;

use App\Business\Helpers\Address;
use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Central\User;
use App\Rules\ValidPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TenantAdministratorsController extends Controller
{
    public function index(Tenant $tenant)
    {
        return Inertia::render('Central/Tenants/Administrators', [
            'id' => $tenant->ID,
            'name' => $tenant->Name,
        ]);
    }

    public function create(Request $request, Tenant $tenant)
    {
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
        $user = User::firstOrNew('email', $request->input('email'));

        $addingToExisting = $user->exists;

        if (!$addingToExisting) {
            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');
            $user->save();
        }

        $user->tenants()->attach($tenant->ID);

        // Send an email
        if ($addingToExisting) {
            // Email notifying the user they have been added to a new tenant
        } else {
            // Email a signed link to allow the user to continue setup
        }

        $request->session()->flash('flash_bag.area.success', $user->first_name . ' has been added as an administrator for ' . $tenant->Name . '.');
        return Redirect::route('central.tenants.administrators', [$tenant]);

        // Check if user exists, if so add association and send an email
        // If no, create user, add association, send email with signed link to make password etc
    }

    public function delete(Request $request)
    {
        // Abort if trying to delete current user
        // Delete association
    }
}
