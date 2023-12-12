<?php

use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

//uses(RefreshDatabase::class);
//uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);
uses(\Illuminate\Foundation\Testing\DatabaseTruncation::class);

//uses(\Tests\TenantTestCase::class);

test('runs a simulated full payment run for a tenant', function () {
    /** @var Tenant $tenant */
    $tenant = tenant();

    // Set up configuration for Payments V2
    $day = \Carbon\Carbon::now()->day;
    $tenant->use_payments_v2 = true;
    $tenant->fee_calculation_date = $day;
    $tenant->squad_fee_calculation_date = $day;
    $tenant->save();

    // Set up squads
    $squad1 = new \App\Models\Tenant\Squad();
    $squad1->SquadName = 'Squad 1';
    $squad1->fee = 7000; // £70.00;
    $squad1->save();

    $squad2 = new \App\Models\Tenant\Squad();
    $squad2->SquadName = 'Squad 2';
    $squad2->fee = 5000; // £50.00;
    $squad2->save();

    // Create a user
    $user = \App\Models\Tenant\User::factory()->create();
    $user->initJournal();
    $user->refresh();

    $journal = $user->journal;

    $clubMembershipClass = \App\Models\Tenant\ClubMembershipClass::where('Type', \App\Enums\ClubMembershipClassType::CLUB)->first();
    $ngb = \App\Models\Tenant\ClubMembershipClass::where('Type', \App\Enums\ClubMembershipClassType::NGB)->first();

    // Create
    /** @var Illuminate\Database\Eloquent\Collection $members */
    $members = \App\Models\Tenant\Member::factory()->count(3)->create([
        'NGBCategory' => $ngb->ID,
        'ClubCategory' => $clubMembershipClass->ID, ]
    );

    foreach ($members as $member) {
        /** @var \App\Models\Tenant\Member $member */
        $member->squads()->attach($squad1, [
            'Paying' => true,
        ]);

        $member->user()->associate($user);

        $member->save();
    }

    // Dispatch Sync the calculation jobs
    \App\Jobs\PaySumSquadFees::dispatchSync($tenant);
    \App\Jobs\PaySumFees::dispatchSync($tenant);

    expect($user->journal->getBalance()->getAmount())->toBe('-21000');
});
