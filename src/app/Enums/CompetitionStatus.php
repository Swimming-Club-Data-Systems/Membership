<?php

namespace App\Enums;

enum CompetitionStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case PAUSED = 'paused';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';
}
