<?php

namespace App\Exceptions\Accounting;

class TransactionCouldNotBeProcessed extends BaseException
{
    public function __construct($message = null)
    {
        parent::__construct('Double Entry Transaction could not be processed. '.$message);
    }
}
