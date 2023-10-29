<?php

namespace App\Enums;

enum Stroke: string
{
    case INDIVIDUAL_MEDLEY = 'individual_medley';
    case MEDLEY = 'medley';
    case FREESTYLE = 'freestyle';
    case BACKSTROKE = 'backstroke';
    case BREASTSTROKE = 'breaststroke';
    case BUTTERFLY = 'butterfly';
    case CUSTOM = 'custom';
}
