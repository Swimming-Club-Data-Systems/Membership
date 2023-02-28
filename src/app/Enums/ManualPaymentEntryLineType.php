<?php

namespace App\Enums;

enum ManualPaymentEntryLineType: string
{
    case CREDIT = 'credit';
    case DEBIT = 'debit';
}
