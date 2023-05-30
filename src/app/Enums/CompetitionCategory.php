<?php

namespace App\Enums;

enum CompetitionCategory: string
{
    case OPEN = 'open';
    case MALE = 'male';
    case FEMALE = 'female';
    case MIXED = 'mixed';
    case BOY = 'boy';
    case GIRL = 'girl';
}
