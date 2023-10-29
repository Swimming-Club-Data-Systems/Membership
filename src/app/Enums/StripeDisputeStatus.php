<?php

namespace App\Enums;

enum StripeDisputeStatus: string
{
    // Current status of dispute. Possible values are warning_needs_response, warning_under_review, warning_closed, needs_response, under_review, charge_refunded, won, or lost.
    case WARNING_NEEDS_RESPONSE = 'warning_needs_response';
    case WARNING_UNDER_REVIEW = 'warning_under_review';
    case WARNING_CLOSED = 'warning_closed';
    case NEEDS_RESPONSE = 'needs_response';
    case UNDER_REVIEW = 'under_review';
    case CHARGE_REFUNDED = 'charge_refunded';
    case WON = 'won';
    case LOST = 'lost';
}
