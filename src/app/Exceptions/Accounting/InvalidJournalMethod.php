<?php

namespace App\Exceptions\Accounting;

class InvalidJournalMethod extends BaseException
{
    public $message = 'Journal methods must be credit or debit';
}
