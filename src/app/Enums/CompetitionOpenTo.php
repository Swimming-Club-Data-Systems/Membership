<?php

namespace App\Enums;

enum CompetitionOpenTo: string
{
    case MEMBERS = 'members';
    case GUESTS = 'guests';
    case MEMBERS_AND_GUESTS = 'members_and_guests';
}
