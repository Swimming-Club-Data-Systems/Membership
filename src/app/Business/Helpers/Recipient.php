<?php

namespace App\Business\Helpers;

class Recipient
{
    public string $unsubscribe_link;

    public function __construct(
        public string $name,
        public string $email,
    ) {
    }
}
