<?php

namespace App\Enums;

enum CompetitionCourse: string
{
    case SHORT = 'short';
    case LONG = 'long';
    case IRREGULAR = 'irregular';
    case OPEN_WATER = 'open_water';
    case NOT_APPLICABLE = 'not_applicable';
}
