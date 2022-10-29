<?php

namespace App\Exceptions\Accounting;

class JournalAlreadyExists extends BaseException
{
    public $message = 'Journal already exists.';
}
