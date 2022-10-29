<?php

namespace App\Exceptions\Accounting;

use Exception;

class BaseException extends Exception
{
    public function __construct($message = null)
    {
        parent::__construct($message ?: $this->message, 0, null);
    }
}
