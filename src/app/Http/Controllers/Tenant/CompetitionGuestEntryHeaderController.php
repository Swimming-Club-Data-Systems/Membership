<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompetitionGuestEntryHeaderController extends Controller
{
    public function new(Competition $competition, Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Competitions/Entries/NewGuestEntryHeader', [
            'user' => $user != null ? [
                'first_name' => $user->Forename,
                'last_name' => $user->Surname,
                'email' => $user->email,
            ] : null,
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
            ],
        ]);
    }

    public function create()
    {

    }
}
