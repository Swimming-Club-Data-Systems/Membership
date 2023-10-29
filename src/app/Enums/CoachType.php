<?php

namespace App\Enums;

enum CoachType: string
{
    case LEAD_COACH = 'LEAD_COACH';
    case COACH = 'COACH';
    case ASSISTANT_COACH = 'ASSISTANT_COACH';
    case TEACHER = 'TEACHER';
    case HELPER = 'HELPER';
    case ADMINISTRATOR = 'ADMINISTRATOR';
}
