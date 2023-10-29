<?php

namespace App\Enums;

use App\Enums\Attributes\Description;
use App\Enums\Concerns\GetsAttributes;

/**
 * SportSystems two character EventCodes for standard events
 */
enum EventCode: int
{
    use GetsAttributes;

    #[Description('50m Freestyle')]
    case Freestyle50 = 1;
    #[Description('100m Freestyle')]
    case Freestyle100 = 2;
    #[Description('200m Freestyle')]
    case Freestyle200 = 3;
    #[Description('400m Freestyle')]
    case Freestyle400 = 4;
    #[Description('800m Freestyle')]
    case Freestyle800 = 5;
    #[Description('1500m Freestyle')]
    case Freestyle1500 = 6;
    #[Description('50m Breaststroke')]
    case Breaststroke50 = 7;
    #[Description('100m Breaststroke')]
    case Breaststroke100 = 8;
    #[Description('200m Breaststroke')]
    case Breaststroke200 = 9;
    #[Description('50m Butterfly')]
    case Butterfly50 = 10;
    #[Description('100m Butterfly')]
    case Butterfly100 = 11;
    #[Description('200m Butterfly')]
    case Butterfly200 = 12;
    #[Description('50m Backstroke')]
    case Backstroke50 = 13;
    #[Description('100m Backstroke')]
    case Backstroke100 = 14;
    #[Description('200m Backstroke')]
    case Backstroke200 = 15;
    #[Description('200m Individual Medley')]
    case IndividualMedley200 = 16;
    #[Description('400m Individual Medley')]
    case IndividualMedley400 = 17;
    #[Description('100m Individual Medley')]
    case IndividualMedley100 = 29;
    #[Description('150m Individual Medley')]
    case IndividualMedley150 = 37;
}
