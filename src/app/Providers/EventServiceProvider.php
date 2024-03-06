<?php

namespace App\Providers;

use App\Events\Tenant\CompetitionCreated;
use App\Listeners\StripeEventListener;
use App\Listeners\Tenant\PopulateBasicCompetition;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Laravel\Cashier\Events\WebhookReceived;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        WebhookReceived::class => [
            StripeEventListener::class,
        ],
        CompetitionCreated::class => [
            PopulateBasicCompetition::class,
        ],
        \App\Events\Tenant\MemberCreating::class => [
            \App\Listeners\Tenant\InitialiseNewMemberDetails::class,
        ],
        \App\Events\Tenant\MemberCreated::class => [
            \App\Listeners\Tenant\PopulateNewMemberDetails::class,
        ],
        \App\Events\Tenant\SquadMoveCreated::class => [
            \App\Listeners\Tenant\SendSquadMoveCreatedNotification::class,
        ],
        \App\Events\Tenant\SquadMoveUpdated::class => [
            \App\Listeners\Tenant\SendSquadMoveUpdatedNotification::class,
        ],
        \App\Events\Tenant\SquadMoveDeleted::class => [
            \App\Listeners\Tenant\SendSquadMoveDeletedNotification::class,
        ],
        \App\Events\Tenant\CompetitionFileDeleted::class => [
            \App\Listeners\Tenant\DeleteCompetitionFileFromStorage::class,
        ],
        \App\Events\Tenant\CompetitionEntryCreated::class => [
            \App\Listeners\Tenant\SendCompetitionEntryCreatedNotification::class,
        ],
        \App\Events\Tenant\CompetitionEntryUpdated::class => [
            \App\Listeners\Tenant\SendCompetitionEntryUpdatedNotification::class,
        ],
        \App\Events\Tenant\CompetitionEntryVetoed::class => [
            \App\Listeners\Tenant\SendCompetitionEntryVetoedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
