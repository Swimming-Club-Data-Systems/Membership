<?php

namespace App\Models\Tenant;

use App\Enums\CoachType;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property CoachType::class $Type
 */
class Coach extends Pivot
{
    protected $casts = [
        'Type' => CoachType::class,
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
