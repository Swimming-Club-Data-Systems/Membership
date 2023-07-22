<?php

namespace App\Enums;

enum CompetitionEntryCancellationReason: string
{
    case TOO_SLOW = 'too_slow';
    case TOO_FAST = 'too_fast';
    case INVALID_AT_ENTRY = 'invalid_at_entry';
    case REJECTED = 'rejected';
    case MEDICAL = 'medical';
    case MEMBER_DECLINED_SELECTION = 'member_declined_selection';
    case GENERIC_REFUND = 'generic_refund';
    case GENERIC_NO_REFUND = 'generic_no_refund';
}
