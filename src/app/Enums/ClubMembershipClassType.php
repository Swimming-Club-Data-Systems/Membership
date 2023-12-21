<?php

namespace App\Enums;

use App\Enums\Attributes\Description;
use App\Enums\Concerns\GetsAttributes;

/**
 * Club membership class types
 */
enum ClubMembershipClassType: string
{
    use GetsAttributes;

    #[Description('National Governing Body Membership')]
    case NGB = 'national_governing_body';
    #[Description('Club Membership')]
    case CLUB = 'club';
}
