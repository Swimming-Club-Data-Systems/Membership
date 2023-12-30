<?php

namespace App\Traits;

trait JobBackoff
{
    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 60, 120, 240, 480];
    }

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 5;
}
