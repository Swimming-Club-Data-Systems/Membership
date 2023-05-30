<?php

namespace App\Listeners\Tenant;

use App\Enums\CompetitionCategory;
use App\Enums\EventCode;
use App\Enums\Stroke;
use App\Events\Tenant\CompetitionCreated;
use App\Models\Tenant\CompetitionEvent;
use App\Models\Tenant\CompetitionSession;

class PopulateBasicCompetition
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CompetitionCreated $event): void
    {
        // Create a single session, with date gala date
        // Populate the session with OPEN and FEMALE events

        /** @var CompetitionSession $session */
        $session = $event->competition->sessions()->create([
            'name' => 'Session',
            'sequence' => 1,
            'start_time' => $event->competition->gala_date,
            'end_time' => $event->competition->gala_date,
        ]);

        $events = [
            // OPEN CATEGORY (FORMERLY MEN)
            [
                'name' => '50m Freestyle',
                'stroke' => Stroke::FREESTYLE,
                'distance' => 50,
                'event_code' => EventCode::Freestyle50,
                'sequence' => 1,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '100m Freestyle',
                'stroke' => Stroke::FREESTYLE,
                'distance' => 100,
                'event_code' => EventCode::Freestyle100,
                'sequence' => 2,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '200m Freestyle',
                'stroke' => Stroke::FREESTYLE,
                'distance' => 200,
                'event_code' => EventCode::Freestyle200,
                'sequence' => 3,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '400m Freestyle',
                'stroke' => Stroke::FREESTYLE,
                'distance' => 400,
                'event_code' => EventCode::Freestyle400,
                'sequence' => 4,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '800m Freestyle',
                'stroke' => Stroke::FREESTYLE,
                'distance' => 800,
                'event_code' => EventCode::Freestyle800,
                'sequence' => 5,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '1500m Freestyle',
                'stroke' => Stroke::FREESTYLE,
                'distance' => 1500,
                'event_code' => EventCode::Freestyle1500,
                'sequence' => 6,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '50m Breaststroke',
                'stroke' => Stroke::BREASTSTROKE,
                'distance' => 50,
                'event_code' => EventCode::Breaststroke50,
                'sequence' => 7,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '100m Breaststroke',
                'stroke' => Stroke::BREASTSTROKE,
                'distance' => 100,
                'event_code' => EventCode::Breaststroke100,
                'sequence' => 8,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '200m Breaststroke',
                'stroke' => Stroke::BREASTSTROKE,
                'distance' => 200,
                'event_code' => EventCode::Breaststroke200,
                'sequence' => 9,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '50m Butterfly',
                'stroke' => Stroke::BUTTERFLY,
                'distance' => 50,
                'event_code' => EventCode::Butterfly50,
                'sequence' => 10,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '100m Butterfly',
                'stroke' => Stroke::BUTTERFLY,
                'distance' => 100,
                'event_code' => EventCode::Butterfly100,
                'sequence' => 11,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '200m Butterfly',
                'stroke' => Stroke::BUTTERFLY,
                'distance' => 200,
                'event_code' => EventCode::Butterfly200,
                'sequence' => 12,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '50m Backstroke',
                'stroke' => Stroke::BACKSTROKE,
                'distance' => 50,
                'event_code' => EventCode::Backstroke50,
                'sequence' => 13,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '100m Backstroke',
                'stroke' => Stroke::BACKSTROKE,
                'distance' => 100,
                'event_code' => EventCode::Backstroke100,
                'sequence' => 14,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '200m Backstroke',
                'stroke' => Stroke::BACKSTROKE,
                'distance' => 200,
                'event_code' => EventCode::Backstroke200,
                'sequence' => 15,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '100m Individual Medley',
                'stroke' => Stroke::INDIVIDUAL_MEDLEY,
                'distance' => 100,
                'event_code' => EventCode::IndividualMedley100,
                'sequence' => 16,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '150m Individual Medley',
                'stroke' => Stroke::INDIVIDUAL_MEDLEY,
                'distance' => 150,
                'event_code' => EventCode::IndividualMedley150,
                'sequence' => 17,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '200m Individual Medley',
                'stroke' => Stroke::INDIVIDUAL_MEDLEY,
                'distance' => 200,
                'event_code' => EventCode::IndividualMedley200,
                'sequence' => 18,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            [
                'name' => '400m Individual Medley',
                'stroke' => Stroke::INDIVIDUAL_MEDLEY,
                'distance' => 400,
                'event_code' => EventCode::IndividualMedley400,
                'sequence' => 19,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::OPEN,
            ],
            // Female
            [
                'name' => '50m Freestyle',
                'stroke' => Stroke::FREESTYLE,
                'distance' => 50,
                'event_code' => EventCode::Freestyle50,
                'sequence' => 20,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '100m Freestyle',
                'stroke' => Stroke::FREESTYLE,
                'distance' => 100,
                'event_code' => EventCode::Freestyle100,
                'sequence' => 21,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '200m Freestyle',
                'stroke' => Stroke::FREESTYLE,
                'distance' => 200,
                'event_code' => EventCode::Freestyle200,
                'sequence' => 22,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '400m Freestyle',
                'stroke' => Stroke::FREESTYLE,
                'distance' => 400,
                'event_code' => EventCode::Freestyle400,
                'sequence' => 23,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '800m Freestyle',
                'stroke' => Stroke::FREESTYLE,
                'distance' => 800,
                'event_code' => EventCode::Freestyle800,
                'sequence' => 24,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '1500m Freestyle',
                'stroke' => Stroke::FREESTYLE,
                'distance' => 1500,
                'event_code' => EventCode::Freestyle1500,
                'sequence' => 25,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '50m Breaststroke',
                'stroke' => Stroke::BREASTSTROKE,
                'distance' => 50,
                'event_code' => EventCode::Breaststroke50,
                'sequence' => 26,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '100m Breaststroke',
                'stroke' => Stroke::BREASTSTROKE,
                'distance' => 100,
                'event_code' => EventCode::Breaststroke100,
                'sequence' => 27,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '200m Breaststroke',
                'stroke' => Stroke::BREASTSTROKE,
                'distance' => 200,
                'event_code' => EventCode::Breaststroke200,
                'sequence' => 28,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '50m Butterfly',
                'stroke' => Stroke::BUTTERFLY,
                'distance' => 50,
                'event_code' => EventCode::Butterfly50,
                'sequence' => 29,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '100m Butterfly',
                'stroke' => Stroke::BUTTERFLY,
                'distance' => 100,
                'event_code' => EventCode::Butterfly100,
                'sequence' => 30,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '200m Butterfly',
                'stroke' => Stroke::BUTTERFLY,
                'distance' => 200,
                'event_code' => EventCode::Butterfly200,
                'sequence' => 31,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '50m Backstroke',
                'stroke' => Stroke::BACKSTROKE,
                'distance' => 50,
                'event_code' => EventCode::Backstroke50,
                'sequence' => 32,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '100m Backstroke',
                'stroke' => Stroke::BACKSTROKE,
                'distance' => 100,
                'event_code' => EventCode::Backstroke100,
                'sequence' => 33,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '200m Backstroke',
                'stroke' => Stroke::BACKSTROKE,
                'distance' => 200,
                'event_code' => EventCode::Backstroke200,
                'sequence' => 34,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '100m Individual Medley',
                'stroke' => Stroke::INDIVIDUAL_MEDLEY,
                'distance' => 100,
                'event_code' => EventCode::IndividualMedley100,
                'sequence' => 35,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '150m Individual Medley',
                'stroke' => Stroke::INDIVIDUAL_MEDLEY,
                'distance' => 150,
                'event_code' => EventCode::IndividualMedley150,
                'sequence' => 36,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '200m Individual Medley',
                'stroke' => Stroke::INDIVIDUAL_MEDLEY,
                'distance' => 200,
                'event_code' => EventCode::IndividualMedley200,
                'sequence' => 37,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
            [
                'name' => '400m Individual Medley',
                'stroke' => Stroke::INDIVIDUAL_MEDLEY,
                'distance' => 400,
                'event_code' => EventCode::IndividualMedley400,
                'sequence' => 38,
                'entry_fee' => $event->competition->default_entry_fee,
                'category' => CompetitionCategory::FEMALE,
            ],
        ];

        $modelEvents = collect($events)->map(function ($item) {
            return new CompetitionEvent($item);
        })->all();

        $session->events()->saveMany($modelEvents);
    }
}
