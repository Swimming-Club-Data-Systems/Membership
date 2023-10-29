<?php

namespace App\Enums;

use App\Enums\Attributes\Description;
use App\Enums\Concerns\GetsAttributes;

enum CoachType: string
{
    use GetsAttributes;

    #[Description('Lead Coach')]
    case LEAD_COACH = 'LEAD_COACH';
    #[Description('Coach')]
    case COACH = 'COACH';
    #[Description('Assistant Coach')]
    case ASSISTANT_COACH = 'ASSISTANT_COACH';
    #[Description('Teacher')]
    case TEACHER = 'TEACHER';
    #[Description('Helper')]
    case HELPER = 'HELPER';
    #[Description('Administrator')]
    case ADMINISTRATOR = 'ADMINISTRATOR';
}
