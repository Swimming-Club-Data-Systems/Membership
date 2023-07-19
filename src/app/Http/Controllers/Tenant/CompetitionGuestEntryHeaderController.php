<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\Sex;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionGuestEntrant;
use App\Models\Tenant\CompetitionGuestEntryHeader;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
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

    public function create(Competition $competition, Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email:rfc,dns'],
            'swimmers' => [
                'first_name' => ['required', 'string', 'max:50'],
                'last_name' => ['required', 'string', 'max:50'],
                'date_of_birth' => ['required', 'date', 'after_or_equal:1900-01-01', 'before_or_equal:today'],
                'sex' => ['required', new Enum(Sex::class)],
            ],
        ]);

        DB::beginTransaction();

        try {
            $guestEntryHeader = new CompetitionGuestEntryHeader();
            if ($request->user()) {
                $guestEntryHeader->user()->associate($request->user());
            } else {
                $guestEntryHeader->first_name = $request->string('first_name');
                $guestEntryHeader->last_name = $request->string('last_name');
                $guestEntryHeader->email = $request->string('email');
            }
            $guestEntryHeader->save();

            $request->collect('swimmers')->each(function ($swimmer) use ($guestEntryHeader) {
                $entrant = new CompetitionGuestEntrant();
                $entrant->first_name = $swimmer['first_name'];
                $entrant->last_name = $swimmer['last_name'];
                $entrant->date_of_birth = $swimmer['date_of_birth'];
                $entrant->sex = $swimmer['sex'];
                $entrant->competitionGuestEntryHeader()->associate($guestEntryHeader);
                $entrant->save();
            });

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Report error in flash message
            throw $e;
        }

    }
}
