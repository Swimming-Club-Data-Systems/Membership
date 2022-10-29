<?php

namespace App\Exceptions\Accounting;

class InvalidJournalEntryValue extends BaseException
{
    public $message = 'Journal transaction entries must be a positive value';
}
