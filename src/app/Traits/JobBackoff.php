<?php

namespace App\Traits;

trait JobBackoff
{
    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [1, 5, 10];
    }
}
