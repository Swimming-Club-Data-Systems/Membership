<?php

namespace App\Models\Tenant;

use App\Enums\CompetitionEntryCancellationReason;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property CompetitionEntry $entry
 * @property CompetitionEvent $event
 * @property float $entry_time
 * @property int $amount
 * @property int $amount_refunded
 * @property CompetitionEntryCancellationReason $cancellation_reason
 * @property string $notes
 */
class CompetitionEventEntry extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'cancellation_reason' => CompetitionEntryCancellationReason::class,
    ];
}
